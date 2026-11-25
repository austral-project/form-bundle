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

/**
 * Austral Field checkbox Input.
 * @template T of object
 *
 * @extends SwitchField<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class CheckboxField extends SwitchField
{
  /**
   * @var array<string, mixed>
   */
  protected array $switchOptions = [];

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
   * CheckboxField constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, array $options = [])
  {
    parent::__construct($fieldname, $options);
    if($this->isDefaultTemplate)
    {
      $this->options["template"]["path"] = "checkbox-field.html.twig";
    }
  }

}