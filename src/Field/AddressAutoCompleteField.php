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

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field AddressAutoComplete Input.
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class AddressAutoCompleteField extends Field
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
   * AddressAutoCompleteField constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, array $options = [])
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = TextType::class;
  }

  /**
   * @inheritDoc
   */
  public function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('autocomplete-options', function (OptionsResolver $resolverChild) {
      $resolverChild->setDefaults(array(
          "id"                        =>  "autocomplete-address",
          "type"                      =>  "all",
          "requestLimit"              =>  10,
          "limitEnabled"              =>  3,
        )
      );
      $resolverChild->setAllowedTypes('requestLimit', array('float', 'int'));
      $resolverChild->setAllowedTypes('limitEnabled', array('float', 'int'));
      $resolverChild->setAllowedTypes('id', array('string'));
      $resolverChild->setAllowedTypes('type', array('string'));
      $resolverChild->setAllowedValues('type', ['all', 'housenumber', 'street', 'locality', "municipality"]);
    });
  }

  /**
   * @inheritDoc
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    if($this->options['autocomplete-options']["type"] === "all")
    {
      $this->options['autocomplete-options']["type"] = "";
    }
    $fieldOptions["attr"]["data-autocomplete-address"] = json_encode($this->options['autocomplete-options']);
    return $fieldOptions;
  }

}