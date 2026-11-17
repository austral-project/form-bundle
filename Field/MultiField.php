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
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class MultiField extends Field
{

  /**
   * @var array<string, FieldInterface<T>>
   */
  protected array $fields = [];

  /**
   * @param string $fieldname
   * @param array<string, FieldInterface<T>> $fields
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, array $fields, array $options = []): self
  {
    return new self($fieldname, $fields, $options);
  }

  /**
   * MultiField constructor.
   *
   * @param string $fieldname
   * @param array<string, FieldInterface<T>> $fields
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, array $fields, array $options = [])
  {
    parent::__construct($fieldname, $options);

    if($this->isDefaultTemplate)
    {
      $this->options["template"]["path"] = "multi-fields.html.twig";
    }

    /** @var FieldInterface<T> $field */
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
   * @inheritDoc
   */
  public function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('separator', null);
    $resolver->setAllowedTypes('separator', array('string', 'null'));
  }

  /**
   * @return string|null
   */
  public function getFieldSeparator(): ?string
  {
    return $this->options["separator"];
  }

  /**
   * @return array<string, FieldInterface<T>>
   */
  public function getFields(): array
  {
    return $this->fields;
  }

}