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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field Switch Input.
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class SwitchField extends Field
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
   * Switch constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, array $options = [])
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = CheckboxType::class;
    if($this->isDefaultTemplate)
    {
      $this->options["template"]["path"] = "switch-field.html.twig";
    }
  }

  /**
   * @inheritDoc
   */
  public function configureOptions(OptionsResolver $resolver): void
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
   * @return array<string, mixed>
   */
  public function getSwitchOptions(): array
  {
    return $this->options['switch-options'];
  }

  /**
   * @return string
   */
  public function getSwitchStyles(): string
  {
    $styles = [];
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

  /**
   * @inheritDoc
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions["attr"]['autocomplete'] = AustralTools::getValueByKey(AustralTools::getValueByKey($fieldOptions, "attr", []), "autocomplete", "off");
    return $fieldOptions;
  }

}