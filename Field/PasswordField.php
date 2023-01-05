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

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\String\u;

/**
 * Austral Field Password Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class PasswordField extends Field
{

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $options = array()): PasswordField
  {
    return new self($fieldname, $options);
  }

  /**
   * PasswordField constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = $this->options['repeat'] ? RepeatedType::class : PasswordType::class;
    $this->widgetInput = !$this->options['repeat'];
    $this->options["entitled"] = $this->options['repeat'] ? "{$this->options["entitled"]}.master" : "{$this->options["entitled"]}";
    if($this->isDefaultTemplate)
    {
      $this->options['template']["path"] = $this->options['repeat'] ? "repeated-field.html.twig" : "";
    }
    if($this->options['repeat']) {
      $this->options['type'] = PasswordType::class;
    }
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults(array(
        "type"      =>  null,
        "repeat"    =>  true,
        "match"     =>  "#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$#"
      )
    )->addAllowedTypes("repeat", array('bool'))
    ->addAllowedTypes("match", array('null', "string"));
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $optionsFields =  parent::getFieldOptions();
    if($this->options['repeat'])
    {
      $optionsFields['type'] = $this->options['type'];
      if(!array_key_exists("first_options", $optionsFields))
      {
        $optionsFields['first_options']['label'] = "{$this->options["entitled"]}.first";
        $optionsFields['first_options']['attr'] = array(
          "autocomplete"  =>  "new-password",
          "data-password" =>  ""
        );
      }
      if(!array_key_exists("second_options", $optionsFields))
      {
        $optionsFields['second_options']['label'] = "{$this->options["entitled"]}.second";
        $optionsFields['second_options']['attr'] = array(
          "autocomplete"  =>  "new-password",
          "data-password" =>  ""
        );
      }
      if(!array_key_exists("invalid_message", $optionsFields))
      {
        $optionsFields['invalid_message'] = "field.error.{$this->fieldname}.repeat";
      }
    }
    else
    {
      $optionsFields['attr']["autocomplete"] = "new-password";
    }
    return $optionsFields;
  }

  /**
   * @return string|null
   */
  public function pregMatch(): ?string
  {
    return $this->options['match'];
  }

  /**
   * @return string
   */
  public function getClassFieldSubType(): string
  {
    return u($this->getSymfonyFormSubTypeName())->camel()->toString();
  }

  /**
   * @return string
   */
  public function getSymfonyFormSubTypeName(): string
  {
    return join('', array_slice(explode('\\', $this->options['type']), -1));
  }

}