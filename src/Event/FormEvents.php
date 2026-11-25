<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Event;

/**
 * Austral Form Events.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class FormEvents
{
  /**
   * FormMapperFactory::create(), before the configuration of the mapper (FormMapperEvent, root mapper only).
   * Replaces austral.event.form.add_auto_fields_before.
   */
  public const string CONFIGURE_BEFORE = 'austral.form.configure_before';

  /**
   * FormMapperFactory::create(), after the configuration, for the mapper then each mapper of its collections
   * (FormMapperEvent). Replaces austral.event.form.add_auto_fields_after.
   */
  public const string CONFIGURE_AFTER = 'austral.form.configure_after';

  /**
   * AustralFormType, for each field before it is added to the form (FieldBuildEvent): after the constraints of
   * the guesser, before the constraints the field adds itself (Field::getAutoConstraints()), so a listener can
   * configure the field they are computed from. Replaces austral.event.form.field.configuration.
   */
  public const string FIELD_BUILD = 'austral.form.field.build';

  /**
   * FormHandler::process(), once the request is handled, before the validity is read (FormProcessEvent), for
   * the form then each Austral sub-form. Replaces austral.event.form.validate.
   */
  public const string SUBMIT = 'austral.form.submit';

  /**
   * FormHandler::process(), the form being valid, before the save (FormProcessEvent). Replaces
   * austral.event.form.update_before.
   */
  public const string SAVE_BEFORE = 'austral.form.save_before';

  /**
   * FormHandler::process(), after the save (FormProcessEvent). Replaces austral.event.form.update_after.
   */
  public const string SAVE_AFTER = 'austral.form.save_after';

  private function __construct()
  {
  }
}
