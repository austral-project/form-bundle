<?php
/*
 * This file is part of the App package.
 *
 * (c) Yipikai <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Twig\Components;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent("Austral:Form:Row", template: '@AustralForm/Components/row.html.twig')]
final class Row extends Base
{
  public string $tag = "div";
  public ?string $typeField = null;
  public string $name = "fieldname";
  public bool $reverse = false;
  public bool $disabled = false;
  public bool $required = false;
  public string $labelPosition = "default";
  public string $fieldClassContainer = "";

  #[PreMount]
  public function preMount(array $data): array
  {
    $data = parent::preMount($data);
    $resolver = new OptionsResolver();
    $resolver->setIgnoreUndefined(true);

    $resolver->setDefault('tag', "label")
      ->addAllowedTypes("tag", array("null", "string"));

    $resolver->setDefault('name', "label")
      ->addAllowedTypes("name", array("null", "string"));

    $resolver->setDefault('reverse', false)
      ->addAllowedTypes("reverse", array("boolean"));

    $resolver->setDefault('disabled', false)
      ->addAllowedTypes("disabled", array("boolean"));

    $resolver->setDefault('required', false)
      ->addAllowedTypes("required", array("boolean"));

    $resolver->setDefault('labelPosition', "label")
      ->setAllowedValues('labelPosition', ['default', 'external'])
      ->addAllowedTypes("labelPosition", array("null", "string"));


    return $resolver->resolve($data) + $data;
  }

  #[PostMount]
  public function postMount(array $data): array
  {
    $data = parent::postMount($data);

    if(!array_key_exists("class", $data))
    {
      $data["class"] = "";
    }
    if($this->reverse)
    {
      $data["class"] .= " reverse";
    }
    if($this->disabled)
    {
      $data["class"] .= " disabled";
    }
    if($this->required)
    {
      $data["class"] .= " required";
    }

    if($this->typeField)
    {
      $this->fieldClassContainer .= " field-container-type-{$this->typeField}";
    }
    return $data;
  }

}