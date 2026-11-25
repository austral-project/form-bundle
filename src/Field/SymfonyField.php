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

use Symfony\Component\Form\FormTypeInterface;

/**
 * Austral Symfony Field.
 *
 * Any Symfony form type in the Austral layout, with the presentation of an Austral field. Replaces the 3.x
 * FormTypeField too (a type instance is accepted, its class is used).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class SymfonyField extends Field
{
  /**
   * @param string                         $name
   * @param string|FormTypeInterface<mixed> $type    Class of a form type, or a form type; anything else is rejected
   * @param array<string, mixed>           $options
   */
  public function __construct(string $name, string|FormTypeInterface $type, array $options = [])
  {
    $type = $type instanceof FormTypeInterface ? $type::class : $type;
    if (!is_a($type, FormTypeInterface::class, true))
    {
      throw new \InvalidArgumentException(\sprintf('The type of the field "%s" must be a class implementing "%s", "%s" given.', $name, FormTypeInterface::class, $type));
    }
    $this->symfonyType = $type;
    parent::__construct($name, $options);
  }

  /**
   * @param string                         $name
   * @param string|FormTypeInterface<mixed> $type
   * @param array<string, mixed>           $options
   *
   * @return self
   */
  public static function create(string $name, string|FormTypeInterface $type, array $options = []): self
  {
    return new self($name, $type, $options);
  }
}
