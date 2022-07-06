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
use Austral\FormBundle\Field\Base\FieldInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field Multi Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class MultiField extends Field
{

  /**
   * @var array
   */
  protected array $fields = array();

  /**
   * @param $fieldname
   * @param array $fields
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $fields, array $options = array()): MultiField
  {
    return new self($fieldname, $fields, $options);
  }

  /**
   * SymfonyField constructor.
   *
   * @param string $fieldname
   * @param array $fields
   * @param array $options
   */
  public function __construct(string $fieldname, array $fields, array $options = array())
  {
    parent::__construct($fieldname, $options);

    if($this->isDefaultTemplate)
    {
      $this->options['templatePath'] = "multi-fields.html.twig";
    }

    /**
     * @var string $name
     * @var FieldInterface $field
     */
    foreach($fields as $field)
    {
      if($field->getEntitled() == "fields.{$field->getFieldname()}.entitled")
      {
        $field->setEntitled("fields.{$fieldname}.{$field->getFieldname()}.entitled");
      }
      $field->setFieldname("{$fieldname}_{$field->getFieldname()}");
      $this->fields[$field->getFieldname()] = $field;
    }
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('separator', null);
    $resolver->setAllowedTypes('separator', array('string', 'null'));
  }

  public function getFieldSeparator()
  {
    return $this->options["separator"];
  }

  /**
   * @return array
   */
  public function getFields(): array
  {
    return $this->fields;
  }

}