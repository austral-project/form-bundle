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

/**
 * Austral Field Template.
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class TemplateField extends Field
{

  /**
   * @var array<string, mixed>
   */
  protected array $vars = [];

  /**
   * @param string $fieldname
   * @param string $templatePath
   * @param array<string, mixed> $options
   * @param array<string, mixed> $vars
   *
   * @return self<object>
   */
  public static function create(string $fieldname, string $templatePath, array $options = [], array $vars = []): self
  {
    return new self($fieldname, $templatePath, $options, $vars);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param string $templatePath
   * @param array<string, mixed> $options
   * @param array<string, mixed> $vars
   */
  public function __construct(string $fieldname, string $templatePath, array $options = [], array $vars = [])
  {
    parent::__construct($fieldname, $options);
    $this->options["template"]["path"] = $templatePath;
    $this->isDefaultTemplate = false;
    $this->symfonyFormType = "template";
    $this->usedGeneratedForm = false;
    $this->vars = $vars;
  }

  /**
   * @return array<string, mixed>
   */
  public function getVars(): array
  {
    return $this->vars;
  }

  /**
   * @param array<string, mixed> $vars
   *
   * @return $this
   */
  public function setVars(array $vars): self
  {
    $this->vars = $vars;
    return $this;
  }
  
}