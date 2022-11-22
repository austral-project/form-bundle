<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Mapper;

use Austral\EntityBundle\Entity\EntityInterface;
use Austral\FormBundle\Mapper\Base\MapperElementInterface;
use Austral\FormBundle\Mapper\Base\MapperElement;

use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Austral Fieldset Mapper.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class Fieldset extends MapperElement implements MapperElementInterface
{

  const POSITION_MASTER = "master";
  const POSITION_RIGHT = "right";
  const POSITION_LEFT = "left";
  const POSITION_BOTTOM = "bottom";
  const POSITION_NONE = "none";

  /**
   * @var string
   */
  protected string $positionName = self::POSITION_MASTER;

  /**
   * @var bool
   */
  protected bool $viewName = true;

  /**
   * @var string|null
   */
  protected ?string $entitled = null;

  /**
   * @var array
   */
  protected array $attr = array();

  /**
   * @var bool
   */
  protected bool $collapse = false;

  /**
   * @var array
   */
  protected array $translateArguments = array();

  /**
   * @var \Closure|null
   */
  protected ?\Closure $closureTranslateArgument = null;

  /**
   * Choices constructor.
   *
   * @param string $name
   * @param MapperElementInterface $parent
   * @param string|null $entitled
   */
  public function __construct(string $name, MapperElementInterface $parent, ?string $entitled = null)
  {
    $this->name = $name;
    $this->entitled = $entitled ?? $name;
    $slugger = new AsciiSlugger();
    $this->keyname = "fieldset-".strtolower($slugger->slug($name));
    $this->parent = $parent;
  }

  /**
   * @param string|null $afterId
   *
   * @return string
   */
  public function getFieldsetId(string $afterId = null): string
  {
    if($this->positionName == self::POSITION_MASTER)
    {
      $slugger = new AsciiSlugger();
      $fieldsetId = "fieldset-".strtolower($slugger->slug($this->name)).($afterId ? "-{$afterId}" : null);
    }
    else
    {
      $fieldsetId = "fieldset-{$this->positionName}";
    }
    return $fieldsetId;
  }

  /**
   * @param string $positionName
   *
   * @return $this
   */
  public function setPositionName(string $positionName = self::POSITION_MASTER): Fieldset
  {
    $this->positionName = $positionName;
    return $this;
  }

  /**
   * @return string
   */
  public function getPositionName(): string
  {
    return $this->positionName;
  }

  /**
   * @return string|null
   */
  public function getEntitled(): ?string
  {
    return $this->entitled;
  }

  /**
   * @param string|null $entitled
   *
   * @return Fieldset
   */
  public function setEntitled(?string $entitled): Fieldset
  {
    $this->entitled = $entitled;
    return $this;
  }

  /**
   * @param bool $viewName
   *
   * @return $this
   */
  public function setViewName(bool $viewName = true): Fieldset
  {
    $this->viewName = $viewName;
    return $this;
  }

  /**
   * @return bool
   */
  public function viewName(): bool
  {
    return $this->viewName;
  }

  /**
   * @return array
   */
  public function getAttr(): array
  {
    return $this->attr;
  }

  /**
   * @param array $attr
   *
   * @return Fieldset
   */
  public function setAttr(array $attr): Fieldset
  {
    $this->attr = $attr;
    return $this;
  }

  /**
   * @param EntityInterface|array|null $object
   *
   * @return array
   */
  public function getTranslateArguments($object = null): array
  {
    if($this->closureTranslateArgument)
    {
      $this->closureTranslateArgument->call($this, $this, $object);
    }
    return $this->translateArguments;
  }

  /**
   * @param \Closure $closureTranslateArgument
   *
   * @return Fieldset
   */
  public function setClosureTranslateArgument(\Closure $closureTranslateArgument): Fieldset
  {
    $this->closureTranslateArgument = $closureTranslateArgument;
    return $this;
  }

  /**
   * @param string $key
   * @param string $value
   *
   * @return Fieldset
   */
  public function addTranslateArguments(string $key, string $value): Fieldset
  {
    $this->translateArguments[$key] = $value;
    return $this;
  }

  /**
   * @param array $translateArguments
   *
   * @return Fieldset
   */
  public function setTranslateArguments(array $translateArguments): Fieldset
  {
    $this->translateArguments = $translateArguments;
    return $this;
  }

  /**
   * @return MapperElementInterface|FormMapper
   */
  public function end(): MapperElementInterface
  {
    return $this->parent;
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
   * @return Fieldset
   */
  public function setCollapse(bool $collapse): Fieldset
  {
    $this->collapse = $collapse;
    return $this;
  }

}