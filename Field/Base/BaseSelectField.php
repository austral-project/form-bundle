<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Field\Base;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Base Field Select Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class BaseSelectField extends Field
{

  /**
   * @var array
   */
  protected array $choices = array();

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param array $choices
   * @param array $options
   */
  public function __construct($fieldname, array $choices = array(), array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = ChoiceType::class;
    $this->choices = $choices;
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver->setDefault("multiple", false)
      ->setAllowedTypes('multiple', array('boolean'));

    $resolver->setDefault('select-options', function (OptionsResolver $resolverChild) {
      $resolverChild->setDefaults(array(
          "enabled"                     =>  true,
          "tag"                         =>  false,
          "addItems"                    =>  true,
          "editItems"                   =>  true,
          "removeItems"                 =>  true,
          "function"                    =>  null,
          "searchEnabled"               =>  true,
          "searchResultLimit"           =>  10,
          "placeholder"                 =>  true,
          "placeholderValue"            =>  null,
          "searchPlaceholderValue"      =>  null,
          "delimiter"                   =>  ", ",
          "duplicateItemsAllowed"       =>  false,
        )
      );
      $resolverChild->setAllowedTypes('function', array('string', \Closure::class, "null"))
        ->setAllowedTypes('enabled', array('boolean'))
        ->setAllowedTypes('tag', array('boolean'))
        ->setAllowedTypes('placeholder', array('boolean'))
        ->setAllowedTypes('placeholderValue', array('string', "null"))
        ->setAllowedTypes('searchPlaceholderValue', array('string', "null"))
        ->setAllowedTypes('searchEnabled', array('boolean'))
        ->setAllowedTypes('searchResultLimit', array('integer'))
        ->setAllowedTypes('addItems', array('boolean'))
        ->setAllowedTypes('editItems', array('boolean'))
        ->setAllowedTypes('duplicateItemsAllowed', array('boolean'))
        ->setAllowedTypes('delimiter', array('string'))
        ->setAllowedTypes('removeItems', array('boolean'));
    });
  }

  /**
   * @return string
   */
  public function getSymfonyFormTypeClass(): string
  {
    return parent::getSymfonyFormTypeClass().($this->options["multiple"] ? "-multi" : "")." field-select-type".($this->options["multiple"] ? "-multi" : "").($this->tagsActivated() ? "-tag" : "");
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions['choices'] = array_key_exists("choices", $fieldOptions) ? $fieldOptions["choices"] : $this->choices;
    $fieldOptions["multiple"] = array_key_exists("multiple", $fieldOptions) ? $fieldOptions["multiple"] : $this->options["multiple"];

    if($this->options["multiple"]) {
      $this->options['select-options']['removeItemButton'] = true;
    }

    $fieldOptions["attr"]['data-select'] = $this->options['select-options']['enabled'];
    if($fieldOptions["attr"]['data-select']) {
      $fieldOptions["attr"]["data-select-options"] = json_encode($this->options['select-options']);
    }
    return $fieldOptions;
  }

  /**
   * @return bool
   */
  public function tagsActivated(): bool
  {
    return $this->options["select-options"]['tag'];
  }

  /**
   * @return string|null|\Closure
   */
  public function getChoicesToTagsActivated($object = null, $default = array())
  {
    if($this->options["select-options"]['function'])
    {
      return $this->executeClosureFunction($this->options["select-options"]['function'], $object, $default);
    }
    else
    {
      return $this->executeClosureFunction($this->options["getter"], $object, $default);
    }
  }

  /**
   * @param array $choices
   *
   * @return $this
   */
  public function setChoices(array $choices = array()): BaseSelectField
  {
    $this->choices = $choices;
    return $this;
  }

}