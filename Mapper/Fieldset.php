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

use Austral\Contracts\Entity\EntityInterface;
use Austral\FormBundle\Mapper\Base\MapperElementInterface;
use Austral\FormBundle\Mapper\Base\MapperElement;

use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Austral Fieldset Mapper.
 * @template T of object
 *
 * @extends MapperElement<T>
 * @implements MapperElementInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class Fieldset extends MapperElement implements MapperElementInterface
{

  const string POSITION_MASTER = "master";
  const string POSITION_RIGHT = "right";
  const string POSITION_LEFT = "left";
  const string POSITION_BOTTOM = "bottom";
  const string POSITION_NONE = "none";

  /**
   * @var string
   */
  public static string $keynameBefore = "fieldset-";

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
   * @var bool
   */
  protected bool $collapse = false;

  /**
   * @var array<string, string>
   */
  protected array $translateArguments = [];

  /**
   * @var \Closure(EntityInterface<T>|array<string|int, mixed>|null): array<string, string>|null
   */
  protected ?\Closure $closureTranslateArgument = null;

  /**
   * Choices constructor.
   *
   * @param string $name
   * @param MapperElementInterface<T> $parent
   * @param string|null $entitled
   */
  public function __construct(string $name, MapperElementInterface $parent, ?string $entitled = null)
  {
    parent::__construct($name, $parent);
    $this->entitled = $entitled ?? $name;
  }

  /**
   * @param string|null $afterId
   *
   * @return string
   */
  public function getFieldsetId(?string $afterId = null): string
  {
    if($this->positionName == self::POSITION_MASTER)
    {
      $slugger = new AsciiSlugger();
      $fieldsetId = self::$keynameBefore.strtolower($slugger->slug($this->name)).($afterId ? "-{$afterId}" : null);
    }
    else
    {
      $fieldsetId = self::$keynameBefore.$this->positionName;
    }
    return $fieldsetId;
  }

  /**
   * @param string $positionName
   *
   * @return $this
   */
  public function setPositionName(string $positionName = self::POSITION_MASTER): self
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
   * @return $this
   */
  public function setEntitled(?string $entitled): self
  {
    $this->entitled = $entitled;
    return $this;
  }

  /**
   * @return string
   */
  public function getFieldname(): string
  {
    return $this->keyname;
  }

  /**
   * @param bool $viewName
   *
   * @return $this
   */
  public function setViewName(bool $viewName = true): self
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
   * @return FormMapper<T>
   */
  public function end(): FormMapper
  {
    /** @var FormMapper<T> $parent */
    $parent = $this->parent;
    return $parent;
  }

  /**
   * @param EntityInterface<T>|array<string|int, mixed>|null $object
   *
   * @return array<string, string>
   */
  public function getTranslateArguments(EntityInterface|array|null $object = null): array
  {
    if($this->closureTranslateArgument)
    {
      $this->closureTranslateArgument->call($this, $object);
    }
    return $this->translateArguments;
  }

  /**
   * @param \Closure(EntityInterface<T>|array<string|int, mixed>|null): array<string, string>|null $closureTranslateArgument
   *
   * @return $this
   */
  public function setClosureTranslateArgument(?\Closure $closureTranslateArgument = null): self
  {
    $this->closureTranslateArgument = $closureTranslateArgument;
    return $this;
  }

  /**
   * @param string $key
   * @param string $value
   *
   * @return $this
   */
  public function addTranslateArguments(string $key, string $value): self
  {
    $this->translateArguments[$key] = $value;
    return $this;
  }

  /**
   * @param array<string, string> $translateArguments
   *
   * @return $this
   */
  public function setTranslateArguments(array $translateArguments): self
  {
    $this->translateArguments = $translateArguments;
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
  public function setCollapse(bool $collapse): self
  {
    $this->collapse = $collapse;
    return $this;
  }

}