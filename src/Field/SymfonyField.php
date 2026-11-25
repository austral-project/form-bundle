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
 * Austral Field Symfony Input.
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class SymfonyField extends Field
{

  /**
   * @param string $fieldname
   * @param class-string<FormTypeInterface> $symfonyFormType
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, string $symfonyFormType, array $options = []): self
  {
    return new self($fieldname, $symfonyFormType, $options);
  }

  /**
   * SymfonyField constructor.
   *
   * @param string $fieldname
   * @param class-string<FormTypeInterface> $symfonyFormType
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, string $symfonyFormType, array $options = [])
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = $symfonyFormType;
  }

}