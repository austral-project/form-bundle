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

use Symfony\Component\Form\Extension\Core\Type\TimeType;

/**
 * Austral Field Time Input.
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class TimeField extends Field
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
   * TextField constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, array $options = [])
  {
    $options = array_merge(array(
      "fieldOptions"  =>  array(
        "widget"  =>  "single_text"
      )
    ), $options);
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = TimeType::class;
  }

}