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

/**
 * Display-only field (like TemplateField): part of the layout, never of the form.
 */
final class TemplateLikeField extends Field
{
  protected bool $displayOnly = true;

  /**
   * @param string               $name
   * @param string               $template
   * @param array<string, mixed> $options
   *
   * @return self
   */
  public static function create(string $name, string $template, array $options = []): self
  {
    return new self($name, ['template' => $template] + $options);
  }
}
