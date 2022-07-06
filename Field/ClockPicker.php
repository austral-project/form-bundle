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
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @link https://seballot.github.io/spectrum/
 * @final
 */
class ClockPicker extends Field
{

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $options = array()): ClockPicker
  {
    return new self($fieldname, $options);
  }

  /**
   * ClockPicker constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = TimeType::class;
    if(!$this->picto())
    {
      $this->options['picto'] = "austral-picto-clock";
    }
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
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
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions['widget'] = "single_text";
    $fieldOptions['with_seconds'] = false;
    $fieldOptions['html5'] = false;
    $fieldOptions["attr"]["data-clockpicker"] = true;
    $fieldOptions["attr"]["class"] = AustralTools::getValueByKey(AustralTools::getValueByKey($fieldOptions, "attr", array()), "class")." timepicker-ui-input";
    $fieldOptions["attr"]["data-clockpicker-options"] = json_encode($this->options['clockpicker-options']);
    return $fieldOptions;
  }

}