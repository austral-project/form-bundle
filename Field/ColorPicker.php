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

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field ColorPicker Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @link https://seballot.github.io/spectrum/
 * @final
 */
class ColorPicker extends Field
{

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $options = array()): ColorPicker
  {
    return new self($fieldname, $options);
  }

  /**
   * ColorPicker constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = TextType::class;
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('colorpicker-options', function (OptionsResolver $resolverChild) {
      $resolverChild->setDefaults(array(
          "preview"                 =>  true,
          "swatches"               =>   array(
            "#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff",
            "#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f",
            "#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc",
            "#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd",
            "#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0",
            "#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79",
            "#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47",
            "#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"
          ),
        )
      );

      $resolverChild->setAllowedTypes('preview', array('boolean'));
      $resolverChild->setAllowedTypes('swatches', array('array', "null"));

      $resolverChild->setDefault("components", function(OptionsResolver $resolverChild) {
        $resolverChild->setDefaults(array(
            "palette"                 =>  false,
            "opacity"                 =>  false,
            "hue"                     =>  false
          )
        );
        $resolverChild->setAllowedTypes('palette', array('boolean'));
        $resolverChild->setAllowedTypes('opacity', array('boolean'));
        $resolverChild->setAllowedTypes('hue', array('boolean'));
      });

      $resolverChild->setDefault("interaction", function(OptionsResolver $resolverChild) {
        $resolverChild->setDefaults(array(
            "hex"                     =>  false,
            "rgba"                    =>  false,
            "hsla"                    =>  false,
            "hsva"                    =>  false,
            "cmyk"                    =>  false,
            "input"                   =>  false,
            "clear"                   =>  false,
            "save"                    =>  false
          )
        );
        $resolverChild->setAllowedTypes('hex', array('boolean'));
        $resolverChild->setAllowedTypes('rgba', array('boolean'));
        $resolverChild->setAllowedTypes('hsla', array('boolean'));
        $resolverChild->setAllowedTypes('hsva', array('boolean'));
        $resolverChild->setAllowedTypes('cmyk', array('boolean'));
        $resolverChild->setAllowedTypes('input', array('boolean'));
        $resolverChild->setAllowedTypes('clear', array('boolean'));
        $resolverChild->setAllowedTypes('save', array('boolean'));
      });

    });
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions["attr"]["data-colorpicker"] = true;
    $fieldOptions["attr"]["data-colorpicker-element"] = "#colorPicker-{$this->fieldname}";
    $colorPickerOptions = $this->options['colorpicker-options'];
    foreach ($colorPickerOptions as $key => $value)
    {
      if($value === null)
      {
        unset($colorPickerOptions[$key]);
      }
    }
    $fieldOptions["attr"]["data-colorpicker-options"] = json_encode($colorPickerOptions);
    return $fieldOptions;
  }

}