<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Layout;

use Austral\FormBundle\Mapper\FormMapper;

/**
 * Austral Fieldset.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class Fieldset extends LayoutNode
{
  public const string TYPE = 'fieldset';

  public const string POSITION_MASTER = 'master';
  public const string POSITION_RIGHT = 'right';
  public const string POSITION_LEFT = 'left';
  public const string POSITION_BOTTOM = 'bottom';
  public const string POSITION_NONE = 'none';

  /**
   * @var string
   */
  protected string $position = self::POSITION_MASTER;

  /**
   * @var bool
   */
  protected bool $collapse = false;

  /**
   * @var bool
   */
  protected bool $showLabel = true;

  /**
   * @var array<string, string>
   */
  protected array $labelParameters = [];

  /**
   * @var (\Closure(Fieldset, object|null): array<string, string>)|null
   */
  protected ?\Closure $labelParametersResolver = null;

  /**
   * @param string            $name
   * @param FormMapper        $mapper
   * @param string|false|null $label  Translation key; the name when null
   * @param string            $position
   */
  public function __construct(string $name, FormMapper $mapper, string|false|null $label = null, string $position = self::POSITION_MASTER)
  {
    parent::__construct($name, $mapper, null, $label ?? $name);
    $this->position = $position;
  }

  /**
   * @return string
   */
  public function getPosition(): string
  {
    return $this->position;
  }

  /**
   * @param string $position
   *
   * @return $this
   */
  public function setPosition(string $position): static
  {
    $this->position = $position;

    return $this;
  }

  /**
   * @return bool
   */
  public function isCollapse(): bool
  {
    return $this->collapse;
  }

  /**
   * @param bool $collapse
   *
   * @return $this
   */
  public function setCollapse(bool $collapse = true): static
  {
    $this->collapse = $collapse;

    return $this;
  }

  /**
   * @return bool
   */
  public function isShowLabel(): bool
  {
    return $this->showLabel;
  }

  /**
   * @param bool $showLabel
   *
   * @return $this
   */
  public function setShowLabel(bool $showLabel = true): static
  {
    $this->showLabel = $showLabel;

    return $this;
  }

  /**
   * @param array<string, string> $labelParameters
   *
   * @return $this
   */
  public function setLabelParameters(array $labelParameters): static
  {
    $this->labelParameters = $labelParameters;

    return $this;
  }

  /**
   * The closure receives the fieldset and the data of the form (each entry of a collection) and returns translation parameters.
   *
   * @param (\Closure(Fieldset, object|null): array<string, string>)|null $resolver
   *
   * @return $this
   */
  public function setLabelParametersResolver(?\Closure $resolver): static
  {
    $this->labelParametersResolver = $resolver;

    return $this;
  }

  /**
   * @param object|null $data
   *
   * @return array<string, string>
   */
  public function resolveLabelParameters(?object $data = null): array
  {
    $parameters = $this->labelParameters;
    if ($this->labelParametersResolver)
    {
      $parameters = array_replace($parameters, ($this->labelParametersResolver)($this, $data));
    }

    return $parameters;
  }
}
