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

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;

abstract class Base
{
  protected Translator $translator;
  public array $attr = array();
  public ?string $translationDomain = null;
  public array $translationParameters = array();

  public function __construct(
    #[Autowire('@translator.default')] Translator $translator
  )
  {
    $this->translator = $translator;
  }

  #[PreMount]
  public function preMount(array $data): array
  {
    $resolver = new OptionsResolver();
    $resolver->setIgnoreUndefined(true);

    return $resolver->resolve($data) + $data;
  }

  #[PostMount]
  public function postMount(array $data): array
  {
    foreach ($this->attr as $key => $value) {
      if(in_array($key, array("placeholder", "title")) && $this->translationDomain)
      {
        $data[$key] = $value ? $value : $this->translator->trans($value, $this->translationParameters, $this->translationDomain);
      }
      else
      {
        $data[$key] = $value === false ? $key : $value;
      }
    }
    return $data;
  }

}