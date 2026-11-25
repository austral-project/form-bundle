# Austral Form

[![Version](https://img.shields.io/packagist/v/austral/form-bundle?label=version)](https://packagist.org/packages/austral/form-bundle)
[![CI](https://github.com/austral-project/form-bundle/actions/workflows/ci.yml/badge.svg?branch=4.x)](https://github.com/austral-project/form-bundle/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/packagist/dependency-v/austral/form-bundle/php?version=4.x-dev)](https://packagist.org/packages/austral/form-bundle)
[![Symfony](https://img.shields.io/badge/symfony-7.4-black)](https://symfony.com/releases/7.4)
[![License](https://img.shields.io/github/license/austral-project/form-bundle)](LICENSE)

Form builder and mapper for Austral

> This bundle is part of [Austral](https://github.com/austral-project), a modular back office and CMS for Symfony.

form-bundle describes a form with a fluent API (fieldsets, groups, modals and Austral fields), builds it as a
Symfony form and handles it. It does not style anything: the form exposes a **view contract** that a theme
renders. A minimal theme is provided; the admin theme and the Twig components are in design-bundle.

## Requirements

- PHP 8.4 or higher
- Symfony 7.4

## Installation

```bash
composer require austral/form-bundle
```

If you do not use Symfony Flex, register the bundle in `config/bundles.php`:

```php
return [
    // ...
    Austral\FormBundle\AustralFormBundle::class => ['all' => true],
];
```

The bundle has no configuration. To render the forms with the minimal theme:

```yaml
# config/packages/twig.yaml
twig:
    form_themes: ['@AustralForm/form/austral_layout.html.twig']
```

## Usage

### Describe the form

```php
use Austral\FormBundle\Field;
use Austral\FormBundle\Layout\Fieldset;
use Austral\FormBundle\Layout\Group;
use Austral\FormBundle\Mapper\FormMapper;

$mapper = $mapperFactory->create('form_austral', Page::class, $page, function (FormMapper $mapper) use ($security): void {
    $mapper->addFieldset('fieldset.content')
        ->add(Field\TextField::create('title', ['required' => true, 'picto' => 'austral-picto-text']))
        ->add(Field\WysiwygField::create('content'))
        ->addGroup('dates')
            ->setDirection(Group::DIRECTION_ROW)
            ->add(Field\DateField::create('publishedAt'))
            ->add(Field\SwitchField::create('isPublished', ['title' => 'fields.isPublished.title']))
            ->end()
        ->end();

    $mapper->addFieldset('fieldset.seo', null, Fieldset::POSITION_RIGHT)
        ->setVisible(fn (): bool => $security->isGranted('ROLE_SEO'))
        ->add(Field\TextField::create('refTitle'));
});
```

`$mapperFactory` is the service `austral.form.mapper_factory`. A `FormMapper` is not a service: create one per
form. Fields added to the mapper itself go to a default fieldset at the bottom, without label.

### Build and handle it

```php
use Austral\FormBundle\Form\Type\AustralFormType;

$form = $formFactory->createNamed($mapper->getName(), AustralFormType::class, $page, ['mapper' => $mapper]);

$result = $formHandler->process($form, $request, fn () => $entityManager->flush());
if ($result->isSaved()) {
    // ...
}
```

`createNamed()` keeps the HTML names of the mapper (`form_austral[title]`) and allows several Austral forms on
the same page. `$formHandler` is the service `austral.form.handler`; `process()` returns a `FormResult`
(`getStatus()`: `not_submitted`, `error` or `success`).

### Fields

| Field | Symfony type | Notes |
|-------|--------------|-------|
| `TextField` | `TextType` | |
| `TextareaField` | `TextareaType` | height: `HEIGHT_AUTO`, `HEIGHT_MIDDLE`, `HEIGHT_BIG` |
| `WysiwygField` | `TextareaType` | `wysiwyg_options`: configuration of the editor |
| `NumberField` | `NumberType`, `IntegerType` | option `integer` |
| `SwitchField` | `CheckboxType` | `title`, `summary`, `pictos`, colors; `appearance`: switch or checkbox |
| `ChoiceField` | `ChoiceType` (expanded) | radios or checkboxes, styles by choice |
| `SelectField` | `ChoiceType`, `EnumType` | Choices.js (`select_options`), tags, option `enum` |
| `EntityField` | `EntityType` | `entity_class`, `query_builder`, `choice_label` |
| `DateField` | `DateType` | single text input, date picker |
| `TimeField` | `TimeType` | single text input, clock picker |
| `ColorField` | `TextType` | color picker |
| `PasswordField` | `RepeatedType`, `PasswordType` | `repeat`, `match` (Regex) |
| `UploadField` | `FileType` | size, MIME types, image sizes, croppers |
| `RecaptchaField` | `HiddenType` | reCAPTCHA v2 / v3, always validated |
| `CollectionEmbedField` | `CollectionType` | entries built from a mapper, variants |
| `SymfonyField` | any | any Symfony form type in the Austral layout |
| `TemplateField` | — | display only: a template at its place in the layout |

Options of every field: `label` (default `fields.<name>.entitled`, `false` for none), `required`, `attr`,
`constraints`, `mapped`, `getter`, `setter`, `placeholder`, `picto`, `helper`, `size`, `class`,
`container_class`, `label_position`, `before` / `after` (HTML, template or Twig component), `template`,
`template_vars`, `visible` (boolean, closure, or `[callable, ...arguments]`), `auto_constraints` and
`form_options` (given as they are to the Symfony type, they win over the shortcuts). An unknown option throws.

A field that is not visible, or inside a node that is not visible, is not added to the form: it is neither
rendered nor submitted, so its data is never changed.

### Automatic constraints

With `auto_constraints` (default), a field receives constraints deduced from the Doctrine mapping of the data
class (`DoctrineConstraintGuesser`): `NotNull` for a required field or a column that is not nullable, `Length`
for a string column, `Type` / `DateTime` and `UniqueEntity` for a text field. Some fields add their own: `File`
for `UploadField`, `Regex` for `PasswordField`. A `NotNull` constraint makes the field required.

### Collections

```php
$itemMapper = new FormMapper('item', GalleryItem::class);
$itemMapper->addFieldset('item')
    ->setLabelParametersResolver(fn (Fieldset $fieldset, ?object $item): array => ['%label%' => $item?->getLabel()])
    ->add(Field\TextField::create('label'));

$mapper->add(Field\CollectionEmbedField::create('items', [
    'entry' => ['mapper' => $itemMapper],
    'sortable' => ['value' => 'position', 'editable' => true],
    'button' => 'button.new.item',
]));
```

Each entry is an Austral form with its own layout, constraints and events. With `addVariant()`, a
`CollectionEmbedField` is a container of several collections (one by type), each one usually with a getter and
a setter filtering the same property.

### Custom fields

```php
final class PathField extends TextField
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('prefix', '/');
    }

    protected function getWidget(): array
    {
        return ['prefix' => $this->options['prefix']];
    }
}
```

A field extends `Field` (or `TextField`, `CollectionEmbedField`), sets `$symfonyType` and can override
`configureOptions()`, `getSymfonyOptions()`, `getWidget()` (display data, `form.vars.austral.widget`),
`getAutoConstraints()` and `addToForm()` (to add form listeners).

## View contract

These variables are the contract between form-bundle and the themes. Their names do not change within a major
version.

**`form.vars.austral_layout`**, on the root form and on each entry of a collection: the visible nodes in
display order.

| Key | Nodes | Value |
|-----|-------|-------|
| `type` | all | `fieldset`, `group`, `modal`, `template`, `variants` |
| `id` | all | unique on the page: the id of the form view, then the id of the node (`form_austral-fieldset-content`, `form_austral_items_0-fieldset-item`, `form_austral_items___name__-fieldset-item` in a prototype) |
| `name`, `label`, `attr` | fieldset, group, modal | |
| `children` | fieldset, group, modal, variants | names of the fields (render them with `form_row(form[name])`) or nodes |
| `position`, `collapse`, `show_label`, `label_parameters` | fieldset | `label_parameters` are computed with the data of the form (of the entry) |
| `direction`, `size`, `style` | group | CSS classes |
| `fieldname`, `modal`, `button` | modal | `button`: `label`, `picto`, `class`, `data` |
| `template`, `template_vars`, `austral` | template | display-only field |
| `austral` | variants | presentation of the container (`widget.choices`: choices by category) |

**`form.vars.austral`**, on each field: `picto`, `helper`, `size`, `class`, `container_class`, `field_class`
(short class name in camelCase, `textField`), `label_position`, `before`, `after` (`html`, `template`,
`component`, `parameters`), `template`, `template_vars` and `widget` (display data of the field). `null` on a
form type that is not an Austral field.

**`form.vars.austral_entry`**, on each entry of a `CollectionEmbedField`: `sortable` (position of the entry).

## Events

The names are the constants of `Austral\FormBundle\Event\FormEvents`.

| Event | Dispatched by | Object |
|-------|---------------|--------|
| `austral.form.configure_before` | `FormMapperFactory::create()`, before the configuration | `FormMapperEvent` |
| `austral.form.configure_after` | after the configuration, for the mapper and each mapper of its collections | `FormMapperEvent` |
| `austral.form.field.build` | `AustralFormType`, for each field before it is added (the field can still change) | `FieldBuildEvent` |
| `austral.form.submit` | `FormHandler`, the request being handled, before the validity is read | `FormProcessEvent` |
| `austral.form.save_before` | `FormHandler`, the form being valid, before the save | `FormProcessEvent` |
| `austral.form.save_after` | `FormHandler`, after the save | `FormProcessEvent` |

`submit`, `save_before` and `save_after` are dispatched for the form, then for each entry of its collections,
with the mapper, form and data of the entry. An exception thrown by a listener is not caught.

## Services

| Id | Class |
|----|-------|
| `austral.form.mapper_factory` | `FormMapperFactory` |
| `austral.form.handler` | `FormHandler` |
| `austral.form.constraint_guesser` | `DoctrineConstraintGuesser` (also `ConstraintGuesserInterface`) |

`AustralFormType`, `PresentationTypeExtension` and `RecaptchaValidator` are registered with their tags.

## Documentation

The full documentation is available at https://austral.dev.

## Versions

| Bundle | Symfony | PHP | Branch |
|--------|---------|-----|---------|
| 4.x    | 7.4     | ≥ 8.4 | `4.x` |
| 3.1    | 5.4 / 6.4 | ≥ 8.0 | `3.1` (maintenance) |

See the [CHANGELOG](CHANGELOG.md) and, to upgrade from 3.1, [UPGRADE-4.0](UPGRADE-4.0.md).

## Contributing

Contributions are welcome: see the [contributing guide](https://github.com/austral-project/.github/blob/master/CONTRIBUTING.md). To report a security issue, follow the [security policy](https://github.com/austral-project/.github/blob/master/SECURITY.md).

## License

MIT, see [LICENSE](LICENSE).

## Credits

Created by [Matthieu Beurel](https://www.mbeurel.com). Sponsored by [Yipikai Studio](https://yipikai.studio).
