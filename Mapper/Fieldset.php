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
   * @var array
   */
  protected array $attr = array();

  /**
   * Choices constructor.
   *
   * @param string $name
   * @param MapperElementInterface $parent
   */
  public function __construct(string $name, MapperElementInterface $parent)
  {
    $this->name = $name;
    $slugger = new AsciiSlugger();
    $this->keyname = "fieldset-".strtolower($slugger->slug($name));
    $this->parent = $parent;
  }

  /**
   * @return string
   */
  public function getFieldsetId(): string
  {
    if($this->positionName == self::POSITION_MASTER)
    {
      $slugger = new AsciiSlugger();
      $fieldsetId = "fieldset-".strtolower($slugger->slug($this->name));
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
   * @return MapperElementInterface|FormMapper
   */
  public function end(): MapperElementInterface
  {
    return $this->parent;
  }

}