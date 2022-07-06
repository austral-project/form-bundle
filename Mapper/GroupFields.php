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

use Austral\ToolsBundle\AustralTools;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Austral Group Fields Mapper.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class GroupFields extends MapperElement implements MapperElementInterface
{

  const STYLE_NONE = null;
  const STYLE_BOOLEAN = "boolean-content";
  const STYLE_WHITE = "white";

  const DIRECTION_ROW = "group-row";
  const DIRECTION_COLUMN = "group-column";

  const SIZE_AUTO = 'auto';
  const SIZE_COL_1 = 'col--1';
  const SIZE_COL_2 = 'col--2';
  const SIZE_COL_3 = 'col--3';
  const SIZE_COL_4 = 'col--4';
  const SIZE_COL_5 = 'col--5';
  const SIZE_COL_6 = 'col--6';
  const SIZE_COL_7 = 'col--7';
  const SIZE_COL_8 = 'col--8';
  const SIZE_COL_9 = 'col--9';
  const SIZE_COL_10 = 'col--10';
  const SIZE_COL_11 = 'col--11';
  const SIZE_COL_12 = 'col--12';

  /**
   * @var string|null
   */
  protected ?string $entitled;

  /**
   * @var string|null
   */
  protected ?string $style = self::STYLE_NONE;

  /**
   * @var string|null
   */
  protected ?string $size = self::SIZE_AUTO;

  /**
   * @var array
   */
  protected array $attr = array();

  /**
   * @var string
   */
  protected string $direction = self::DIRECTION_ROW;

  /**
   * Choices constructor.
   *
   * @param string $name
   * @param MapperElementInterface $parent
   * @param string|null $entitled
   */
  public function __construct(string $name, MapperElementInterface $parent, string $entitled = null)
  {
    $this->name = $name;
    $this->entitled = $entitled;
    $slugger = new AsciiSlugger();
    $this->keyname = "group-".strtolower($slugger->slug($name));
    $this->parent = $parent;
    $this->isView = true;
  }

  /**
   * @return string|null
   */
  public function entitled(): ?string
  {
    return $this->entitled;
  }

  /**
   * @return string|null
   */
  public function style(): ?string
  {
    return $this->style;
  }

  /**
   * @return string|null
   */
  public function classCss(): ?string
  {
    return AustralTools::getValueByKey($this->attr, "class", null);
  }

  /**
   * @return string|null
   */
  public function attrString(): ?string
  {
    $attr = $this->attr;
    if(array_key_exists("class", $attr))
    {
      unset($attr["class"]);
    }
    return $this->stringifyArray("", $attr);
  }

  /**
   * @param string $value
   * @param array $array
   *
   * @return string
   */
  protected function stringifyArray(string $value = "", array $array = array()): string
  {
    foreach ($array as $key => $val)
    {
      if(is_array($val))
      {
        $value = $this->stringifyArray($value, $val);
      }
      elseif($val)
      {
        $value .= "{$key}={$val} ";
      }
      else
      {
        $value .= "{$key}";
      }
    }
    return trim($value);
  }

  /**
   * @return string|null
   */
  public function getStyle(): ?string
  {
    return $this->style;
  }

  /**
   * @param string|null $style
   *
   * @return $this
   */
  public function setStyle(?string $style): GroupFields
  {
    $this->style = $style;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getSize(): ?string
  {
    return $this->size;
  }

  /**
   * @param string|null $size
   *
   * @return $this
   */
  public function setSize(?string $size): GroupFields
  {
    $this->size = $size;
    return $this;
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
   * @return $this
   */
  public function setAttr(array $attr): GroupFields
  {
    $this->attr = $attr;
    return $this;
  }

  /**
   * @return string
   */
  public function getDirection(): string
  {
    return $this->direction;
  }

  /**
   * @param string $direction
   *
   * @return $this
   */
  public function setDirection(string $direction): GroupFields
  {
    $this->direction = $direction;
    return $this;
  }

  /**
   * @return MapperElementInterface|FormMapper|FormField|GroupFields
   */
  public function end(): MapperElementInterface
  {
    return $this->parent;
  }

}