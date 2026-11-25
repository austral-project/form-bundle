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

/**
 * Austral Group.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class Group extends LayoutNode
{
  public const string TYPE = 'group';

  public const null STYLE_NONE = null;
  public const string STYLE_BOOLEAN = 'boolean-content';
  public const string STYLE_WHITE = 'white';

  public const string DIRECTION_ROW = 'group-row';
  public const string DIRECTION_COLUMN = 'group-column';

  public const string SIZE_AUTO = 'auto';
  public const string SIZE_COL_1 = 'col--1';
  public const string SIZE_COL_2 = 'col--2';
  public const string SIZE_COL_3 = 'col--3';
  public const string SIZE_COL_4 = 'col--4';
  public const string SIZE_COL_5 = 'col--5';
  public const string SIZE_COL_6 = 'col--6';
  public const string SIZE_COL_7 = 'col--7';
  public const string SIZE_COL_8 = 'col--8';
  public const string SIZE_COL_9 = 'col--9';
  public const string SIZE_COL_10 = 'col--10';
  public const string SIZE_COL_11 = 'col--11';
  public const string SIZE_COL_12 = 'col--12';

  /**
   * @var string
   */
  protected string $direction = self::DIRECTION_ROW;

  /**
   * @var string
   */
  protected string $size = self::SIZE_AUTO;

  /**
   * @var string|null
   */
  protected ?string $style = self::STYLE_NONE;

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
  public function setDirection(string $direction): static
  {
    $this->direction = $direction;

    return $this;
  }

  /**
   * @return string
   */
  public function getSize(): string
  {
    return $this->size;
  }

  /**
   * @param string|null $size
   *
   * @return $this
   */
  public function setSize(?string $size): static
  {
    $this->size = $size ?? self::SIZE_AUTO;

    return $this;
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
  public function setStyle(?string $style): static
  {
    $this->style = $style;

    return $this;
  }
}
