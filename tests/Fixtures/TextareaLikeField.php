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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Field built on a type that is not TextType: the type rules of the guesser do not apply.
 */
final class TextareaLikeField extends Field
{
  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = TextareaType::class;

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
