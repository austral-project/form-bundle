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

#[AsTwigComponent("Austral:Form:Select", template: '@AustralForm/Components/Fields/select.html.twig')]
final class Select extends BaseField
{
  public array $choices = array();
  public bool $multiple = false;

  public string $placeholder = "";
  public array $placeholderAttributes = array();
  public string $placeholderAttributesString = "";
  public array $dataSelectOptions = array(
    "enabled" => true,
    "tag" => false,
    "addItems" => true,
    "editItems" => true,
    "removeItems" => true,
    "function" => null,
    "searchEnabled" => true,
    "searchResultLimit" => 10,
    "placeholder" => true,
    "placeholderValue" => null,
    "searchPlaceholderValue" => null,
    "delimiter" => ", ",
    "duplicateItemsAllowed" => false,
    "shouldSort" => false,
  );

  #[PreMount]
  public function preMount(array $data): array
  {
    $data = parent::preMount($data);
    $resolver = new OptionsResolver();
    $resolver->setIgnoreUndefined(true);

    $resolver->setDefault('choices', array())
      ->addAllowedTypes("choices", array("null", "array"));

    $resolver->setDefault('multiple', false)
      ->addAllowedTypes("multiple", array("boolean"));

    return $resolver->resolve($data) + $data;
  }

  #[PostMount]
  public function postMount(array $data): array
  {
    $data = parent::postMount($data);
    if($this->placeholder && $this->translationDomain)
    {
      $this->placeholder = $this->translator->trans($this->placeholder, $this->translationParameters, $this->translationDomain);
    }

    if(count($this->placeholderAttributes) > 0)
    {
      $placeholderAttributesTmp = array();
      foreach($this->placeholderAttributes as $key => $value)
      {
        $placeholderAttributesTmp[] = $key."=".$value;
      }
      $this->placeholderAttributesString = implode(" ", $placeholderAttributesTmp);
    }

    if(!array_key_exists("data-select", $data))
    {
      $data["data-select"] = "";
    }

    if(array_key_exists("data-select-options", $data) && $data["data-select-options"])
    {
      $this->dataSelectOptions = json_decode($data["data-select-options"], true);
      unset($data["data-select-options"]);
    }
    return $data;
  }


}