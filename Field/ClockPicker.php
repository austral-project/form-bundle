<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Field;

use Austral\FormBundle\Field\Base\Field;

use Austral\ToolsBundle\AustralTools;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field ClockPicker Input.
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class ClockPicker extends Field
{


  /**
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, array $options = []): self
  {
    return new self($fieldname, $options);
  }

  /**
   * ClockPicker constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, array $options = [])
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = TimeType::class;
    if(!$this->picto())
    {
      $this->options['picto'] = "austral-picto-clock";
    }
  }

  /**
   * @inheritDoc
   */
  public function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('clockpicker-options', function (OptionsResolver $resolverChild) {
      $resolverChild->setDefaults(array(
          "clockType"                         =>  "24h",
          "switchToMinutesAfterSelectHour"    =>  true,
          "enableScrollbar"                   =>  false,
        )
      );
      $resolverChild->setAllowedTypes('clockType', array('string'));
      $resolverChild->setAllowedTypes('enableScrollbar', array('boolean'));
      $resolverChild->setAllowedTypes('switchToMinutesAfterSelectHour', array('boolean'));
    });
  }


  /**
   * @inheritDoc
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions['widget'] = "single_text";
    $fieldOptions['with_seconds'] = false;
    $fieldOptions['html5'] = false;
    $fieldOptions["attr"]["data-clockpicker"] = true;
    $fieldOptions["attr"]["class"] = AustralTools::getValueByKey(AustralTools::getValueByKey($fieldOptions, "attr", []), "class")." timepicker-ui-input";
    $fieldOptions["attr"]["data-clockpicker-options"] = json_encode($this->options['clockpicker-options']);
    return $fieldOptions;
  }

}