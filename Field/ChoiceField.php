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
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class ChoiceField extends Field
{

  /**
   * @var array<string, mixed>
   */
  protected array $choices = [];

  /**
   * @param string $fieldname
   * @param array<string, mixed> $choices
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, array $choices, array $options = []): self
  {
    return new self($fieldname, $choices, $options);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $choices
   * @param array<string, mixed> $options
   *
   */
  public function __construct($fieldname, array $choices, array $options = [])
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
   * @return array<int, mixed>
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
   * @param OptionsResolver $resolver
   */
  protected function configureChoices(OptionsResolver $resolver): void
  {
    $resolver->setDefaults(array(
        "value"     =>  null,
        "styles"    =>  []
      )
    );
  }

  /**
   * @inheritDoc
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefault("choice_style", "full");
    $resolver->setDefault("choices_styles", []);
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
   * @return array<string, mixed>
   */
  public function getStylesByChoices(): array
  {
    $stylesValues = [];
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
   * @return array<int, mixed>
   */
  public function getStylesByChoiceKey(string $key): array
  {
    return AustralTools::valueByKey($this->getStylesByChoices(), $key, []);
  }

  /**
   * @return array<string, mixed>
   */
  public function getChoicesValues(): array
  {
    return array_map(function ($values) {
      return $values["value"];
    }, $this->choices);
  }

  /**
   * @inheritDoc
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();

    $fieldOptions['choices'] = AustralTools::valueByKey($fieldOptions, "choices", $this->getChoicesValues());
    $fieldOptions["multiple"] = AustralTools::valueByKey($fieldOptions, "multiple", $this->options["multiple"]);
    $fieldOptions["expanded"] = true;
    $fieldOptions["placeholder"] = false;

    $autocomplete = AustralTools::valueByKey(AustralTools::valueByKey($fieldOptions, "attr", []), "autocomplete", "off");
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