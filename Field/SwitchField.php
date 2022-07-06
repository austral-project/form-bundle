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

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field Switch Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class SwitchField extends Field
{

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $options = array()): SwitchField
  {
    return new self($fieldname, $options);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = CheckboxType::class;
    if($this->isDefaultTemplate)
    {
      $this->options['templatePath'] = "switch-field.html.twig";
    }
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('switch-options', function (OptionsResolver $resolverChild) {
      $resolverChild->setDefaults(array(
          "title"                 =>  null,
          "summary"               =>  null,
        )
      );
      $resolverChild->setDefault("pictos", function (OptionsResolver $resolverSubChild) {
        $resolverSubChild->setDefaults(array(
            "enabled"                 =>  null,
            "disabled"                =>  null,
          )
        );
        $resolverSubChild->setAllowedTypes('enabled', array('string', "null"));
        $resolverSubChild->setAllowedTypes('disabled', array('string', "null"));
      });
      $resolverChild->setDefault("button-colors", function (OptionsResolver $resolverSubChild) {
        $resolverSubChild->setDefaults(array(
            "enabled"                 =>  "var(--color-white-force)",
            "disabled"                =>  "var(--color-white-force)",
          )
        );
        $resolverSubChild->setAllowedTypes('enabled', array('string', "null"));
        $resolverSubChild->setAllowedTypes('disabled', array('string', "null"));
      });
      $resolverChild->setDefault("fond-colors", function (OptionsResolver $resolverSubChild) {
        $resolverSubChild->setDefaults(array(
            "enabled"                 =>  "var(--color-green-40)",
            "disabled"                =>  "var(--color-main-40)",
          )
        );
        $resolverSubChild->setAllowedTypes('enabled', array('string', "null"));
        $resolverSubChild->setAllowedTypes('disabled', array('string', "null"));
      });
      $resolverChild->setAllowedTypes('title', array('string', "null"));
      $resolverChild->setAllowedTypes('summary', array('string', "null"));
    });
  }

  /**
   * @return array|mixed
   */
  public function getSwitchOptions()
  {
    return $this->options['switch-options'];
  }

  /**
   * @return string
   */
  public function getSwitchStyles(): string
  {
    $styles = array();
    foreach($this->options['switch-options']['button-colors'] as $key => $color)
    {
      $styles[] = "--switch-button-color-{$key}:{$color};";
    }
    foreach($this->options['switch-options']['fond-colors'] as $key => $color)
    {
      $styles[] = "--switch-fond-color-{$key}:{$color};";
    }
    return implode(" ", $styles);
  }

}