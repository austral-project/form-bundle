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

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field DatePicker Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @link https://jqueryui.com/datepicker/
 * @final
 */
class DatePicker extends Field
{

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $options = array()): DatePicker
  {
    return new self($fieldname, $options);
  }

  /**
   * DatePicker constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = DateType::class;
    if(!$this->picto())
    {
      $this->options['picto'] = "austral-picto-calendar";
    }
    $this->options["datepicker-options"]['format'] = $this->options["datepicker-options"]['format'] ? : str_replace(array("MM"), array("mm"), $this->options['date-format']);

  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('date-format', "dd/MM/yyyy");
    $resolver->setDefault('datepicker-options', function (OptionsResolver $resolverChild) {
      $resolverChild->setDefaults(array(
          "showWeek"                  =>  false,
          "todayBtn"                  =>  true,
          "clearBtn"                  =>  true,
          "calendarWeeks"             =>  false,
          "autohide"                  =>  true,
          "weekStart"                 =>  1,
          "minDate"                   =>  null,
          "maxDate"                   =>  null,
          "language"                  =>  "en",
          "format"                    =>  "",
          "datesDisabled"             =>  array(),
          "daysOfWeekDisabled"        =>  array()
        )
      );
      $resolverChild->setAllowedTypes('showWeek', array('boolean'));
      $resolverChild->setAllowedTypes('todayBtn', array('boolean'));
      $resolverChild->setAllowedTypes('clearBtn', array('boolean'));
      $resolverChild->setAllowedTypes('calendarWeeks', array('boolean'));
      $resolverChild->setAllowedTypes('autohide', array('boolean'));
      $resolverChild->setAllowedTypes('weekStart', array('int'));
      $resolverChild->setAllowedTypes('minDate', array('string', 'int', "null"));
      $resolverChild->setAllowedTypes('maxDate', array('string', 'int', "null"));
      $resolverChild->setAllowedTypes('language', array('string', "null"));
      $resolverChild->setAllowedTypes('format', array('string'));
      $resolverChild->setAllowedTypes('datesDisabled', array('array'));
      $resolverChild->setAllowedTypes('daysOfWeekDisabled', array('array'));
    });
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions['widget'] = "single_text";
    $fieldOptions['format'] = $this->options['date-format'];
    $fieldOptions['html5'] = false;
    $fieldOptions["attr"]["data-datepicker"] = true;
    $fieldOptions["attr"]["data-datepicker-options"] = json_encode($this->options['datepicker-options']);
    return $fieldOptions;
  }

}