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
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class AddressAutoCompleteField extends Field
{

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $options = array()): AddressAutoCompleteField
  {
    return new self($fieldname, $options);
  }

  /**
   * TextField constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = TextType::class;
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
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
   * @return array
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