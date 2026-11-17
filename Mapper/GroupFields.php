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

/**
 * Austral Group Mapper.
 * @template T of object
 *
 * @extends MapperElement<T>
 * @implements MapperElementInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class GroupFields extends MapperElement implements MapperElementInterface
{

  const null STYLE_NONE = null;
  const string STYLE_BOOLEAN = "boolean-content";
  const string STYLE_WHITE = "white";

  const string DIRECTION_ROW = "group-row";
  const string DIRECTION_COLUMN = "group-column";

  const string SIZE_AUTO = 'auto';
  const string SIZE_COL_1 = 'col--1';
  const string SIZE_COL_2 = 'col--2';
  const string SIZE_COL_3 = 'col--3';
  const string SIZE_COL_4 = 'col--4';
  const string SIZE_COL_5 = 'col--5';
  const string SIZE_COL_6 = 'col--6';
  const string SIZE_COL_7 = 'col--7';
  const string SIZE_COL_8 = 'col--8';
  const string SIZE_COL_9 = 'col--9';
  const string SIZE_COL_10 = 'col--10';
  const string SIZE_COL_11 = 'col--11';
  const string SIZE_COL_12 = 'col--12';

  /**
   * @var string
   */
  public static string $keynameBefore = "group-";

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
   * @var string
   */
  protected string $direction = self::DIRECTION_ROW;

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
    $this->entitled = $entitled;
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
   * @return string
   */
  public function getFieldname(): string
  {
    return $this->keyname;
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

}