# Upgrade from 3.1 to 4.0

## Requirements

- PHP 8.4 minimum
- Symfony 7.4
- Doctrine ORM 3

## Update the dependencies

```diff
- "austral/form-bundle": "^3.1"
+ "austral/form-bundle": "^4.0"
```

All `austral/*` packages must be upgraded to `^4.0` at the same time.

form-bundle no longer requires `austral/contracts`, `austral/entity-bundle` nor `austral/tools-bundle`: require
them in your project if you use them directly.

## Breaking changes

form-bundle is rewritten. The fluent API of the Admin classes is kept (`addFieldset()->add(XxxField::create())->end()`),
the rest changes. The renamed and removed elements have no alias.

### Creating, building and handling a form

The `FormMapper` is no longer a service and the form type no longer keeps it: the mapper is created by the
factory and given to the form type in its `mapper` option.

Before:

```php
$formMapper = $this->container->get('austral.form.mapper');
$formMapper->setObject($page)->setName('form_austral');
// configuration...
$formType = $this->container->get('austral.form.type.master');
$formType->setClass(Page::class)->setFormMapper($formMapper);
$form = $formFactory->createNamed('form_austral', get_class($formType), $page);
```

After:

```php
use Austral\FormBundle\Form\Type\AustralFormType;

$mapper = $mapperFactory->create('form_austral', Page::class, $page, $admin->configureFormMapper(...));
$form = $formFactory->createNamed($mapper->getName(), AustralFormType::class, $page, ['mapper' => $mapper]);
$result = $formHandler->process($form, $request, fn () => $entityManager->flush());
```

| 3.1 | 4.0 |
|-----|-----|
| service `austral.form.mapper` | `austral.form.mapper_factory` (`FormMapperFactory::create()`) |
| service `austral.form.mappers` (`FormMappers`) | removed |
| service `austral.form.type.master` (`FormType`), `FormTypeInterface` | `AustralFormType`, option `mapper` |
| `FormType::setFormMapper()`, `addFormMappers()`, `setClass()`, `setSecurity()` | option `mapper`; `data_class` comes from the mapper |
| `FormMapper::setFormStatus()`, `getFormStatus()`, `setFormSend()` | `FormHandler::process()` returns a `FormResult` (`getStatus()`: `not_submitted`, `error`, `success`) |
| `FormMapper::setFormTypeAction()`, `setRequestMethod()` | options `action` and `method` of the form |
| `FormMapper::setModule()`, `addAction()`, actions, attributes, `errorMessage` | admin-bundle |

The 12 form types of the other bundles that only set a `data_class` (content-block, seo, email, website,
filter) are removed: the entries of a collection are built from a mapper (see *Collections*).

### FormMapper

| 3.1 | 4.0 |
|-----|-----|
| `add($field, $sortable)`, `adds()`, `end()` | unchanged (`add(?Field $field, ?int $order)`) |
| `addFieldset($name, $entitled)` | `addFieldset($name, $label, $position)` |
| `addGroup()` | unchanged |
| `addPopin()` | `addModal()` |
| `getFieldset($name)` | unchanged (it always returned `null`) |
| `getField()`, `removeAllField($name)` | `getField()`, `remove($name)` |
| `allFields()` | `getFields()` |
| `setObject()`, `getObject()`, `setName()` | unchanged |
| `setTranslateDomain()` | `setTranslationDomain()` (default `form`) |
| `setOptions(['validation_groups' => …, 'html5_validate' => …])`, `getOption()` | `setValidationGroups()`, `setHtml5Validate()` |
| `addSubFormMapper()`, `getSubFormMapperByKey()`, `hasSubFormMapperByKey()` | option `entry.mapper` of `CollectionEmbedField`; `getSubMappers()` |
| `getFieldsetByPosition()` (Twig) | `form.vars.austral_layout` |
| `setFieldsMapping()` | removed: the constraint guesser reads the Doctrine mapping |
| `setPathToTemplateDefault()` | removed: the templates are resolved by the theme |
| `setDispatcher()`, `configField()`, `reinit()`, `setObjects()`, `getObjectSluggerClassname()`, `addUniqueField()` | removed |

### Layout

The classes move to the `Layout` namespace:

```diff
- use Austral\FormBundle\Mapper\Fieldset;
- use Austral\FormBundle\Mapper\GroupFields;
- use Austral\FormBundle\Mapper\Modal;
+ use Austral\FormBundle\Layout\Fieldset;
+ use Austral\FormBundle\Layout\Group;
+ use Austral\FormBundle\Layout\Modal;
```

| 3.1 | 4.0 |
|-----|-----|
| `Fieldset::setPositionName()`, `getPositionName()` | `setPosition()`, `getPosition()` (same `POSITION_*` constants) |
| `Fieldset::setViewName()`, `viewName()` | `setShowLabel()`, `isShowLabel()` |
| `Fieldset::setEntitled()`, `getEntitled()` | `setLabel()`, `getLabel()` |
| `Fieldset::addTranslateArguments($key, $value)`, `setTranslateArguments()` | `setLabelParameters(array)` |
| `Fieldset::setClosureTranslateArgument()` | `setLabelParametersResolver()` (see below) |
| `Fieldset::getFieldsetId($afterId)` | `id` of the node in `form.vars.austral_layout`, unique by entry |
| `setIsView()` (fieldset, group, modal) | `setVisible(bool\|Closure)` |
| `setSortable()` | `setOrder()` |
| `GroupFields` | `Group` (same constants) |
| `Modal::getModal()` | `getModalOptions()` |
| `Modal::setModalKeyId()`, `renameModalKeyId()`, option `modal.id` | removed: the id of the modal comes from the view id |
| modal option `button.entitled` | `button.label` |

The closure of the label parameters no longer changes the fieldset: it receives the fieldset and the data of
the form (of each entry in a collection) and **returns** the parameters.

Before:

```php
$fieldset->setClosureTranslateArgument(function (Fieldset $fieldset, $object) {
    $fieldset->addTranslateArguments('%domain%', $object->getDomain()->getName());
});
```

After:

```php
$fieldset->setLabelParametersResolver(fn (Fieldset $fieldset, ?object $object): array => [
    '%domain%' => $object?->getDomain()->getName(),
]);
```

### Fields

The base class is `Austral\FormBundle\Field\Field` (`Field\Base\Field`, `Field\Base\FieldInterface` and
`Field\Base\BaseSelectField` are removed). A custom field extends `Field`, sets `$symfonyType` and overrides
`configureOptions()` (calling the parent). The fields of form-bundle are final, except `TextField` and
`CollectionEmbedField`.

| 3.1 | 4.0 |
|-----|-----|
| `IntegerField::create($name)` | `NumberField::create($name, ['integer' => true])` |
| `CheckboxField` | `SwitchField::create($name, ['appearance' => SwitchField::APPEARANCE_CHECKBOX])` |
| `FormTypeField::create($name, new XxxType())` | `SymfonyField::create($name, XxxType::class)` (an instance is accepted) |
| `EnumField::create($name, Status::class)` | `SelectField::create($name, [], ['enum' => Status::class])` |
| `ColorPicker` | `ColorField` |
| `DatePicker` | `DateField` |
| `ClockPicker` | `TimeField` (option `picker`, enabled by default) |
| `MultiField` | removed: use a `Group` |
| `CountryField`, `AddressAutoCompleteField` | removed |

The options are on a single level and an unknown option throws:

| 3.1 | 4.0 |
|-----|-----|
| `entitled` | `label` (default `fields.<name>.entitled`, `false` for none) |
| `isView` | `visible` |
| `labelPosition` | `label_position` |
| `autoConstraints` | `auto_constraints` |
| `fieldOptions` | `form_options` |
| `container.class`, `group.class` | `container_class` |
| `group.size` | `size` |
| `template.path`, `template.vars` | `template`, `template_vars` |
| `before.path`, `before.html`, `before.twigComponent.name`, `before.twigComponent.parameters` (same for `after`) | `before.template`, `before.html`, `before.component`, `before.parameters` (a string is the HTML) |
| `formMapper`, `modal.id`, `modal.isIn` | removed (set by the mapper) |

| 3.1 | 4.0 |
|-----|-----|
| `getFieldname()` | `getName()` |
| `getEntitled()`, `setEntitled()` | `getLabel()`, `setLabel()` |
| `getRequired()` | `isRequired()` |
| `getIsView()`, `setIsView()` | `isVisible()`, `setVisible()` |
| `getFieldOptions()`, `setFieldOptions()` | `getSymfonyOptions()`, `setOptions(['form_options' => …])` |
| `setOptions()` (replaced every option) | `setOptions()` merges with the current options |
| `getSymfonyFormType()`, `setSymfonyFormType()` | `getSymfonyType()`, property `$symfonyType` |
| `removeContraintByClass()`, `hasContraint()` | `removeConstraint()`, `hasConstraint()` |
| `removeContraintByKey()` | removed |
| `getAutoConstraints()`, `setAutoConstraints()` | `hasAutoConstraints()`, option `auto_constraints`; **`getAutoConstraints()` now returns the constraints the field adds itself** |
| `getTemplatePath()`, `setTemplatePath()` | `getTemplate()`, `setTemplate()` |
| `setGroupSize()`, `getGroupSize()` | `setSize()`, `getSize()` |
| `setGroupClass()`, `getGroupClass()` | option `container_class` |
| `picto()`, `getHelper()`, `getBefore()`, `getAfter()` | `getOption('picto')`… |
| `getClassname()` (CSS class `field-content-…`) | `form.vars.austral.field_class` |
| `getUsedGeneratedForm()`, `setUsedGeneratedForm(false)` | `isDisplayOnly()` (`TemplateField`) |
| `isDefaultTemplate()`, `setDefaultTemplate()`, `isInModal()`, `setIsInModal()`, `getModalId()`, `setModalId()`, `isWidgetInput()`, `getEditorFields()`, `getAttrClass()`, `getSymfonyFormTypeClass()`, `getSymfonyFormTypeName()`, `setFieldname()` | removed |

Options and methods of the fields:

| Field | 3.1 | 4.0 |
|-------|-----|-----|
| `TextareaField` | `SIZE_AUTO`, `SIZE_MIDDLE`, `SIZE_BIG` | `HEIGHT_AUTO`, `HEIGHT_MIDDLE`, `HEIGHT_BIG` (same values) |
| `SwitchField` | `switch-options.title`, `.summary`, `.pictos`, `.button-colors`, `.fond-colors`, `getSwitchOptions()`, `getSwitchStyles()` | `title`, `summary`, `pictos`, `button_colors`, `background_colors`, `form.vars.austral.widget`, `getStyle()` |
| `SelectField`, `EntityField` | `select-options`, `nullValue` | `select_options` (same Choices.js keys), `null_value` |
| `SelectField` | `select-options.function`, `getChoicesToTagsActivated()`, `tagsActivated()` | `tag_choices`, `hasTags()` |
| `SelectField`, `EntityField` | `getThemeName()`, `getThemeDirection()` | `form.vars.austral.widget.theme`, `.direction` |
| `EntityField` | entity class as 2nd argument, also added to the CSS class of the select | 2nd argument unchanged (option `entity_class`), no CSS class |
| `ChoiceField` | `getStylesByChoiceKey()`, `getChoiceStyle()`, `getDirection()` | `getStylesByChoices()`, `form.vars.austral.widget` |
| `WysiwygField` | `wysiwyg-options` | `wysiwyg_options` |
| `ColorField` | `colorpicker-options` | `colorpicker_options` |
| `DateField` | `date-format`, `datepicker-options` | `date_format`, `datepicker_options` |
| `TimeField` | `clockpicker-options` | `clockpicker_options` |
| `PasswordField` | `pregMatch()`, `getClassFieldSubType()` | `getMatch()`, `form.vars.austral.widget.type_class` |
| `TemplateField` | `getVars()`, `setVars()` | `getTemplateVars()`, `setTemplateVars()` |
| `RecaptchaField` | `secretKey`, `publicKey`, `getSecretKey()`, `getScoreLimit()`, `getScoreMessage()` | `secret_key`, `public_key` (required), `getRecaptchaConstraint()` |

`UploadField`:

| 3.1 | 4.0 |
|-----|-----|
| `upload-file-parameters.maxSize`, `.mimeTypes`, `.maxSizeMessage`, `.mimeTypesMessage` | `max_size`, `mime_types`, `max_size_message`, `mime_types_message` |
| `upload-file-parameters.imageSizes` (`minWidth`, `maxWidth`, `minHeight`, `maxHeight`) | `image_sizes` (`min_width`, `max_width`, `min_height`, `max_height`) |
| `upload-file-parameters.file` (`reelFilename`, `path`, `infos.mimeType`, `.sizeHuman`, `.imageSize`, `.aspectRatio`, `.imageDimension`) | `file` (`reel_filename`, `path`, `infos.mime_type`, `.size_human`, `.image_size`, `.aspect_ratio`, `.image_dimension`) |
| `blockSize`, `getSize()`, `DETAIL`, `LIGHT` | `block_size`, `getBlockSize()`, `BLOCK_SIZE_DETAIL`, `BLOCK_SIZE_LIGHT` |
| `cropperFieldKey` | `cropper_field_key` |
| `getUploadFileParameters()`, `setUploadFileParameters()` | the options and their setters (`setMaxSize()`, `setMimeTypes()`, `setImageSizes()`, `setCropper()`, `setFileParameters()`, `setTypeFile()`) |
| `getMaxSize(bool $humanize)` | `getMaxSize()` (as given), `getMaxSizeInBytes()` |
| `extensions()` | `getExtensions()` |

### Collections

The sub-form of a collection is given by its mapper.

Before:

```php
$formMapper->addSubFormMapper('themes', $themeFormMapper);
$formMapper->add(CollectionEmbedField::create('themes', [
    'entitled' => false,
    'container' => ['class' => 'child-color'],
    'entry' => [
        'type' => ThemeFormType::class,
        'attr' => ['formMapperKey' => 'themes'],
    ],
]));
```

After:

```php
$formMapper->add(CollectionEmbedField::create('themes', [
    'container_class' => 'child-color',
    'entry' => ['mapper' => $themeFormMapper],
]));
```

| 3.1 | 4.0 |
|-----|-----|
| `entry.type` + `entry.attr.formMapperKey` + `addSubFormMapper()` | `entry.mapper` (`entry.type` remains for a collection of a plain Symfony type) |
| `addCollectionForm($key, $collection)`, `addCollectionsForms()`, `getCollectionsForms()` | `addVariant($key, $collection)`, `setVariants()`, `getVariants()` |
| `getSelectCollectionChoices()` | `getGroupedChoices()`, `form.vars.austral.widget.choices` |
| `getSortable($object)` (Twig: `field.sortable(entry.vars.data)`) | `getSortable()`, `form.vars.austral_entry.sortable` |
| `allowChild()`, `masterChildren()`, `viewPosition()`, `childrenParameters()`, `betweenInsert()`, `hasSortable()`, `getTitle()`, `getEntitledButton()` | `form.vars.austral.widget` (`allow`, `master_children`, `view_position`, `children_parameters`, `between_insert`, `sortable`, `title`, `button`) |
| `prototype.data` (default `[]`) | default `null` |
| `initCollectionForm()`, `setCollectionParent()`, `getCollectionParent()` | removed |

### Events

The events and their classes are replaced. A listener receives the form, the mapper and the data of each
sub-form: there is no relay to the sub-mappers anymore.

| 3.1 | 4.0 | Object |
|-----|-----|--------|
| `austral.event.form.add_auto_fields_before` (`FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_BEFORE`) | `austral.form.configure_before` (`FormEvents::CONFIGURE_BEFORE`) | `FormMapperEvent` |
| `austral.event.form.add_auto_fields_after` | `austral.form.configure_after` (also for each sub-mapper) | `FormMapperEvent` |
| `austral.event.form.field.configuration` (`FormFieldEvent`) | `austral.form.field.build` | `FieldBuildEvent` |
| `austral.event.form.validate` | `austral.form.submit` | `FormProcessEvent` |
| `austral.event.form.update_before` | `austral.form.save_before` | `FormProcessEvent` |
| `austral.event.form.update_after` | `austral.form.save_after` | `FormProcessEvent` |
| `austral.event.form.init_end`, `austral.event.form.field.pre_set_data` | removed | |

`FormEvent::getFormMapper()` and `FormFieldEvent::getFormMapper()` become `getMapper()`. `FormEvent::setStatus()`
is removed: throw an exception, or add an error to the form in a `submit` listener.

### Rendering

form-bundle no longer contains the templates of the admin, the Twig components nor the Storybook (moved to
design-bundle). The theme reads the view contract (see the README) instead of the mapper:

| 3.1 (Twig) | 4.0 |
|------------|-----|
| `formMapper.fieldsetByPosition(…)`, `fieldset.fields`, `field.…` | `form.vars.austral_layout`, `form_row(form[name])`, `form.vars.austral` |
| `formMapper.subFormMapperByKey(…)` | the entries of the collection render their own `austral_layout` |
| block prefix: name of the mapper | `austral_form` |

### Automatic constraints and visibility

- A string column without length no longer gets a `Length` constraint (it threw).
- A field with `mapped: false` ignores the Doctrine mapping (only the "required" rule applies).
- A column that is not nullable makes the field required; to avoid it, set `auto_constraints: false` on the
  field (`required: 'no'` no longer exists).
- `UploadField` adds `File` (maximum size, MIME types and their extensions, messages) and `Image` (image sizes) itself, even when its property is not mapped by Doctrine. They are computed after `field.build`, so a listener only configures the field (`setMimeTypes()`, `setMaxSize()`…); `getMaxSizeMessage()` and `getMimeTypesMessage()` return `file.error.maxSize` / `file.error.mimeTypes` until a message is given (`hasMaxSizeMessage()`, `hasMimeTypesMessage()`).
- As in 3.x, a field that is not visible is not part of the form; this now also applies to the fields of a
  fieldset, group or modal that is not visible. A hidden field filled by JavaScript must stay visible: use
  `SymfonyField::create($name, HiddenType::class)` or hide it with CSS.

### Recaptcha

`RecaptchaValidator` no longer looks for the field in the request: the `Recaptcha` constraint carries the secret
key, the action and the score limit (set by `RecaptchaField`). The value is now refused when it cannot be
verified (no secret key, no token, Google unreachable, unreadable answer) and when the action of a v3 token is
not the action of the field. Translate the new reasons in the `validators` domain:
`recaptcha.error.missing-input-secret`, `recaptcha.error.unavailable`, `recaptcha.error.action-mismatch`.
