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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\String\u;

/**
 * Austral Field ChoiceRadio Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class ChoiceField extends Field
{

  /**
   * @var array
   */
  protected array $choices = array();

  /**
   * @param $fieldname
   * @param array $choices
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $choices, array $options = array()): ChoiceField
  {
    return new self($fieldname, $choices, $options);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param array $choices
   * @param array $options
   *
   */
  public function __construct($fieldname, array $choices, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = ChoiceType::class;

    $resolver = new OptionsResolver();
    $this->configureChoices($resolver);

    foreach($choices as $key => $values)
    {
      if(!is_array($values))
      {
        $values = array(
          'value'   =>  $values,
        );
      }
      $values = $resolver->resolve($values);
      $this->choices[$key] = $values;
    }

    if($this->isDefaultTemplate)
    {
      $this->options["template"]["path"] = "choiceRadioField.html.twig";
    }
  }

  /**
   * @param bool $value
   *
   * @return array
   */
  protected function getDefaultStyleByValue(bool $value = false): array
  {
    if($value === true)
    {
      return array(
        "--element-choice-current-background:var(--color-green-20)",
        "--element-choice-current-color:var(--color-green-100)",
        "--element-choice-hover-color:var(--color-green-100)"
      );
    }
    return array(
      "--element-choice-current-background:var(color-main-20)",
      "--element-choice-current-color:var(--color-main-100)",
      "--element-choice-hover-color:var(--color-main-100)"
    );
  }

  /**
   * @param $resolver
   */
  protected function configureChoices($resolver)
  {
    $resolver->setDefaults(array(
        "value"     =>  null,
        "styles"    =>  array()
      )
    );
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver->setDefault("choice_style", "full");
    $resolver->setDefault("choices_styles", array());
    $resolver->setDefault("multiple", false);
    $resolver->setDefault("direction", "horizontal");
  }

  /**
   * @return string|null
   */
  public function getChoiceStyle(): ?string
  {
    return $this->options["choice_style"];
  }

  /**
   * @return string|null
   */
  public function getDirection(): ?string
  {
    return $this->options['direction'];
  }

  /**
   * @return string
   */
  public function getSymfonyFormTypeClass(): string
  {
    return parent::getSymfonyFormTypeClass().($this->options['multiple'] ? "-checkbox" : "-radio");
  }

  /**
   * @return array
   */
  public function getStylesByChoices(): array
  {
    $stylesValues = array();
    foreach($this->choices as $key => $values)
    {
      $styles = $values["styles"];
      if(!$styles && $this->options['choices_styles'])
      {
        $styles = $this->options['choices_styles'];
      }
      elseif(!$styles)
      {
        $styles = $this->getDefaultStyleByValue($values['value']);
      }
      $stylesValues[$key] = $styles;
    }
    return $stylesValues;
  }

  /**
   * @param string $key
   *
   * @return array
   */
  public function getStylesByChoiceKey(string $key): array
  {
    return AustralTools::getValueByKey($this->getStylesByChoices(), $key, array());
  }

  /**
   * return array
   */
  public function getChoicesValues(): array
  {
    $choicesValues = array();
    foreach($this->choices as $key => $values)
    {
      $choicesValues[$key] = $values["value"];
    }
    return $choicesValues;
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();

    $fieldOptions['choices'] = array_key_exists("choices", $fieldOptions) ? $fieldOptions["choices"] : $this->getChoicesValues();
    $fieldOptions["multiple"] = array_key_exists("multiple", $fieldOptions) ? $fieldOptions["multiple"] : $this->options["multiple"];
    $fieldOptions["expanded"] = true;
    $fieldOptions["placeholder"] = false;

    $autocomplete = AustralTools::getValueByKey(AustralTools::getValueByKey($fieldOptions, "attr", array()), "autocomplete", "off");
    if(!array_key_exists("choice_attr", $fieldOptions))
    {
      $fieldOptions["choice_attr"] = function($val, $key, $index) use($autocomplete) {
        $key = strtolower($key);
        $key = str_replace(".", " ", $key);
        $key = u($key)->snake()->toString();
        return ['class' => 'field_'.$key, "autocomplete" => $autocomplete];
      };
    }
    return $fieldOptions;
  }

}