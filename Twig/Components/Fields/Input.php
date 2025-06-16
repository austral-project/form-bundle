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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent("Austral:Form:Input", template: '@AustralForm/Components/Fields/input.html.twig')]
final class Input extends BaseField
{
  public string $type = "text";

  #[PreMount]
  public function preMount(array $data): array
  {
    $data = parent::preMount($data);
    $resolver = new OptionsResolver();
    $resolver->setIgnoreUndefined(true);

    $resolver->setDefault('type', "text")
      ->addAllowedTypes("type", array("null", "string"));

    return $resolver->resolve($data) + $data;
  }

  #[PostMount]
  public function postMount(array $data): array
  {
    $data = parent::postMount($data);
    if(in_array($this->type, ["range", "color"]))
    {
      $data["required"] = false;
    }
    if(array_key_exists("checked", $data) && $data["checked"] === true)
    {
      $data["checked"] = "checked";
    }
    return $data;
  }


}