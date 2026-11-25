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

use Austral\Contracts\Entity\EntityInterface;
use Austral\ToolsBundle\AustralTools;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Base Field Select Input.
 * @template T of object
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class BaseSelectField extends Field
{

  /**
   * @var array<string, mixed>
   */
  protected array $choices = [];

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $choices
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, array $choices = [], array $options = [])
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = ChoiceType::class;
    $this->choices = $choices;
  }

  /**
   * @inheritDoc
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefault("multiple", false)
      ->setAllowedTypes('multiple', array('boolean'));

    $resolver->setDefault("nullValue", false)
      ->setAllowedTypes('nullValue', array('boolean'));

    $resolver->setDefault("theme", function(OptionsResolver $subResolver) {
      $subResolver->setDefaults(array(
        "name"          => "default",
        "direction"     => null,
      ));
      $subResolver->addAllowedTypes("name", array('null', "string"));
      $subResolver->addAllowedValues("name", array('default', "choiceList", "choiceElement"));
      $subResolver->addAllowedTypes("direction", array('null', "string"));
      $subResolver->addAllowedValues("direction", array(null, 'vertical', "horizontal"));
    });

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
          "shouldSort"                  =>  false,
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
        ->setAllowedTypes('removeItems', array('boolean'))
        ->setAllowedTypes('shouldSort', array('boolean'));
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
   * @return array<string, mixed>
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions['choices'] = array_key_exists("choices", $fieldOptions) ? $fieldOptions["choices"] : $this->choices;
    $fieldOptions["multiple"] = array_key_exists("multiple", $fieldOptions) ? $fieldOptions["multiple"] : $this->options["multiple"];
    $fieldOptions["attr"]['autocomplete'] = AustralTools::valueByKey(AustralTools::valueByKey($fieldOptions, "attr", []), "autocomplete", "off");
    if($this->options["multiple"]) {
      $this->options['select-options']['removeItemButton'] = true;
    }

    if($this->options["nullValue"]) {
      $fieldOptions["attr"]["data-null-value"] = "true";
    }

    if(!array_key_exists("expanded", $fieldOptions) || $fieldOptions["expanded"] !== true) {
      $fieldOptions["attr"]['data-select'] = $this->options['select-options']['enabled'];
      if($fieldOptions["attr"]['data-select']) {
        $fieldOptions["attr"]["data-select-options"] = json_encode($this->options['select-options']);
      }
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
   * getChoicesToTagsActivated
   *
   * @param EntityInterface<T>|null $object
   * @param mixed $default
   * @return array<string, mixed>
   */
  public function getChoicesToTagsActivated(?EntityInterface $object = null, mixed $default = []): array
  {
    if($this->options["select-options"]['function'])
    {
      $choices = $this->executeClosureFunction($this->options["select-options"]['function'], $object, $default);
    }
    else
    {
      $choices = $this->executeClosureFunction($this->options["getter"], $object, $default);
    }
    foreach($this->choices as $key => $choice) {
      $choices[$key] = $choice;
    }
    return $choices;
  }

  /**
   * @param array<string, mixed> $choices
   *
   * @return $this
   */
  public function setChoices(array $choices = []): self
  {
    $this->choices = $choices;
    return $this;
  }

  /**
   * getThemeName
   *
   * @return string
   */
  public function getThemeName(): string
  {
    return $this->options['theme']["name"];
  }

  /**
   * getThemeDirection
   *
   * @return string|null
   */
  public function getThemeDirection(): ?string
  {
    return $this->options['theme']['direction'];
  }

}