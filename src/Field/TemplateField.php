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
 * Austral Template Field.
 *
 * Display only: rendered from its template at its place in the layout, never added to the Symfony form.
 * A relative template is resolved by the theme, an absolute one (@Bundle/...) is used as it is.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class TemplateField extends Field
{
  /**
   * @var bool
   */
  protected bool $displayOnly = true;

  /**
   * @param string               $name
   * @param string               $template
   * @param array<string, mixed> $options
   * @param array<string, mixed> $vars     Variables given to the template (option "template_vars")
   */
  public function __construct(string $name, string $template, array $options = [], array $vars = [])
  {
    parent::__construct($name, ['template' => $template] + ($vars ? ['template_vars' => $vars] : []) + $options);
  }

  /**
   * @param string               $name
   * @param string               $template
   * @param array<string, mixed> $options
   * @param array<string, mixed> $vars
   *
   * @return self
   */
  public static function create(string $name, string $template, array $options = [], array $vars = []): self
  {
    return new self($name, $template, $options, $vars);
  }
}
