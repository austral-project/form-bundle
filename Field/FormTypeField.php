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
use Symfony\Component\Form\FormTypeInterface;

/**
 * Austral Field FormType Input.
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class FormTypeField extends Field
{

  /**
   * @param string $fieldname
   * @param FormTypeInterface $fieldTypeClass
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, FormTypeInterface $fieldTypeClass, array $options = []): self
  {
    return new self($fieldname, $fieldTypeClass, $options);
  }

  /**
   * FormTypeField constructor.
   *
   * @param string $fieldname
   * @param FormTypeInterface $fieldType
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, FormTypeInterface $fieldType, array $options = [])
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = get_class($fieldType);
    if($this->isDefaultTemplate)
    {
      $this->options["template"]["path"] = "form-type-field.html.twig";
    }
  }

}