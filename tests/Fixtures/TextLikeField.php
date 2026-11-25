<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Fixtures;

use Austral\FormBundle\Field\Field;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Minimal concrete field for the tests of the base class.
 */
final class TextLikeField extends Field
{
  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = TextType::class;

  /**
   * @param string               $name
   * @param array<string, mixed> $options
   *
   * @return self
   */
  public static function create(string $name, array $options = []): self
  {
    return new self($name, $options);
  }
}
