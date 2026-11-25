# Changelog

All notable changes to this bundle are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html). All Austral bundles share the same major and minor version.

## [Unreleased]

Rewrite of the bundle: form-bundle builds and handles the forms and exposes a view contract; the rendering is
free (minimal theme here, admin theme in design-bundle). See [UPGRADE-4.0](UPGRADE-4.0.md) for the
correspondence between the 3.x API and the 4.0 API.

### Added

- `AustralFormType`: a stateless form type, the `FormMapper` is given in its `mapper` option; block prefix `austral_form`.
- `FormMapperFactory` (`austral.form.mapper_factory`) and `FormHandler` (`austral.form.handler`), returning a `FormResult`.
- Layout tree `Layout\Fieldset`, `Layout\Group`, `Layout\Modal`: ids computed from the view id (unique in each entry of a collection and in the prototype), explicit order, visibility by boolean or closure, label parameters computed with the data of the form.
- View contract: `form.vars.austral_layout`, `form.vars.austral` (with `widget`, the display data of a field) and `form.vars.austral_entry`.
- `PresentationTypeExtension`: option `austral` on every form type.
- `DoctrineConstraintGuesser` (`austral.form.constraint_guesser`, `ConstraintGuesserInterface`): the automatic constraints read the Doctrine mapping themselves.
- `Field::getAutoConstraints()` and `Field::addToForm()`: a field adds its own constraints and form listeners.
- Events `FormEvents::CONFIGURE_BEFORE`, `CONFIGURE_AFTER`, `FIELD_BUILD`, `SUBMIT`, `SAVE_BEFORE`, `SAVE_AFTER`, dispatched to the sub-forms without relay.
- `CollectionEmbedField`: option `entry.mapper`, variants (`addVariant()`, `setVariants()`), position of each entry in `form.vars.austral_entry.sortable`.
- `SelectField` option `enum`, `tag_choices`; `NumberField` option `integer`; `SwitchField` option `appearance`; `TimeField` option `picker`.
- Recaptcha: the action of the token is checked (reCAPTCHA v3).
- Minimal theme `@AustralForm/form/austral_layout.html.twig`.
- Unit, integration and rendering tests, `ContainerTest`.

### Changed

- PHP 8.4 and Symfony 7.4 minimum.
- A field has a single level of options; `setOptions()` merges instead of replacing every option.
- A field that is not visible is not added to the form (as in 3.x), also when it is inside a node that is not visible.
- `submit`, `save_before` and `save_after` are dispatched for each entry of a collection with the mapper, the form and the data of the entry (in 3.x, once with the form of the whole collection).
- An exception thrown by a listener or by the save is no longer turned into a form status.
- `RecaptchaValidator` uses the injected HTTP client (TLS verified) and refuses the value whenever it cannot be verified (no secret key, no token, Google unreachable, unreadable answer).
- `File(maxSize)` is added to an `UploadField` even without Doctrine mapping.
- Explicit services, no autowiring nor autoconfiguration.
- The dependencies used by the bundle are declared; `austral/contracts`, `austral/entity-bundle`, `austral/tools-bundle` and `symfony/routing` are no longer required.

### Fixed

- `FormMapper::getFieldset()` always returned `null`.
- The ids of the layout used a static suffix shared by every form (`keynameAfter`) and the prefixes of `self::` instead of `static::`.
- The form type kept the mapper of the first form it built (shared service with a state).
- `uploadFieldEditor()` registered the wrong field.
- `Modal::getModal()` was typed `string` and returned an array.
- `Length(max: null)` was added to a string column without length, which throws.
- The default style of a choice that is not `true` was `var(color-main-20)` (missing `--`).
- The default maximum size of an upload was `-1` when `memory_limit` is `-1`.
- `novalidate` was set on every entry of a collection.
- `RecaptchaValidator` disabled the TLS verification and accepted the form when it could not verify it.

### Removed

- `FormMappers` (`austral.form.mappers`), `FormSubscriber`, `MapperElement`, `FormType` (`austral.form.type.master`), `FormTypeInterface`, the service `austral.form.mapper`.
- `Field\Base\Field`, `Field\Base\FieldInterface`, `Field\Base\BaseSelectField`.
- `IntegerField` (`NumberField`), `CheckboxField` (`SwitchField`), `FormTypeField` (`SymfonyField`), `EnumField` (`SelectField`), `ClockPicker` (`TimeField`), `ColorPicker` (renamed `ColorField`), `DatePicker` (renamed `DateField`), `MultiField`, `CountryField`, `AddressAutoCompleteField`.
- `Event\FormEvent`, `Event\FormFieldEvent` and the events `austral.event.form.*`; `init_end` and `field.pre_set_data`.
- The Twig components, the templates and the Storybook (moved to design-bundle).
- The CSS class of the entity class on `EntityField`.

## [3.1.0] - 2022-09-17

- Changed templatePath option in field
- Added autocomplete to off if not used for checkbox, select, radio

## [3.0.0] - 2021-02-10

- Created new repository and new branch in to Github
- Changed the project License to the MIT
- Writing and publish documentation
- Add to Packagist Service for installation with composer

## 2.x and 1.x

These versions were not open source and have no public documentation.

[Unreleased]: https://github.com/austral-project/form-bundle/compare/v3.1.2...4.x
[3.1.0]: https://github.com/austral-project/form-bundle/compare/v3.0.0...v3.1.0
[3.0.0]: https://github.com/austral-project/form-bundle/releases/tag/v3.0.0
