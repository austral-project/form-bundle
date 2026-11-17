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

use Austral\FormBundle\Field\Base\BaseSelectField;


/**
 * Austral Field Select Input.
 * @template T of object
 *
 * @extends BaseSelectField<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class SelectField extends BaseSelectField
{

  /**
   * @var array<string, mixed>
   */
  protected array $choices = [];

  /**
   * @param string $fieldname
   * @param array<string, mixed> $choices
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, array $choices = [], array $options = []): self
  {
    return new self($fieldname, $choices, $options);
  }

}