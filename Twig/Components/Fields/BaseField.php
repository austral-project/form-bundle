<?php
/*
 * This file is part of the App package.
 *
 * (c) Yipikai <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Twig\Components\Fields;

use Austral\FormBundle\Twig\Components\Base;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;

abstract class BaseField extends Base
{
  public ?string $id = "";
  public ?string $name = "";
  public bool $disabled = false;
  public bool $required = false;
  public ?string $value = "";

  #[PreMount]
  public function preMount(array $data): array
  {
    $data = parent::preMount($data);
    $resolver = new OptionsResolver();
    $resolver->setIgnoreUndefined(true);

    $resolver->setDefault('id', null)
      ->addAllowedTypes("id", array("null", "string"));

    $resolver->setDefault('name', null)
      ->addAllowedTypes("name", array("null", "string"));

    $resolver->setDefault('disabled', false)
      ->addAllowedTypes("disabled", array("boolean"));

    $resolver->setDefault('required', false)
      ->addAllowedTypes("required", array("boolean"));

    $resolver->setDefault('value', null)
      ->addAllowedTypes("value", array("null", "string"));
    return $resolver->resolve($data) + $data;
  }

  #[PostMount]
  public function postMount(array $data): array
  {
    $data = parent::postMount($data);
    if($this->disabled)
    {
      $data["disabled"] = true;
    }
    if($this->required)
    {
      $data["required"] = true;
    }
    return $data;
  }

}