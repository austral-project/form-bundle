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

#[AsTwigComponent("Austral:Form:Label", template: '@AustralForm/Components/label.html.twig')]
final class Label extends Base
{

  public string $tag = "label";
  public string $entitled = "entitled";
  public string $entitledValue = "";

  #[PreMount]
  public function preMount(array $data): array
  {
    $resolver = new OptionsResolver();
    $resolver->setIgnoreUndefined(true);

    $resolver->setDefault('tag', "label")
      ->addAllowedTypes("tag", array("null", "string"));

    $resolver->setDefault('entitled', null)
      ->addAllowedTypes("entitled", array("null", "string"));

    return $resolver->resolve($data) + $data;
  }

  #[PostMount]
  public function postMount(array $data): array
  {
    $data = parent::postMount($data);
    if($this->translationDomain)
    {
      $this->entitledValue = $this->entitled;
      $this->entitled = $this->translator->trans($this->entitled, $this->translationParameters, $this->translationDomain);
    }
    return $data;
  }

}