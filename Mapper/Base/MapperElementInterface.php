<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Mapper\Base;

use Austral\FormBundle\Field\Base\FieldInterface;
use Austral\FormBundle\Mapper\GroupFields;
use Austral\FormBundle\Mapper\Popin;

/**
 * Austral Mapper Element Interface.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface MapperElementInterface
{

  /**
   * @return MapperElementInterface
   */
  public function parent(): MapperElementInterface;

  /**
   * @return MapperElementInterface
   */
  public function end(): MapperElementInterface;

  /**
   * @return string
   */
  public function getName(): string;

  /**
   * @return string
   */
  public function getKeyname(): string;

  /**
   * @param FieldInterface|null $field
   * @param int|null $sortable
   *
   * @return $this
   * @throws \Exception
   */
  public function add(?FieldInterface $field, int $sortable = null): MapperElementInterface;

  /**
   * @param string $name
   * @param string|null $entitled
   *
   * @return GroupFields
   */
  public function addGroup(string $name, string $entitled = null): GroupFields;

  /**
   * @param string $name
   * @param string|null $fielname
   * @param array $attr
   *
   * @return Popin
   * @throws \Exception
   */
  public function addPopin(string $name, string $fielname = null, array $attr = array()): Popin;

  /**
   * @param FieldInterface $field
   *
   * @return $this
   */
  public function configField(FieldInterface $field): MapperElementInterface;

  /**
   * @param FieldInterface $field
   *
   * @return $this
   */
  public function addAllFields(FieldInterface $field): MapperElementInterface;

  /**
   * @param string $fieldname
   *
   * @return $this
   */
  public function removeAllField(string $fieldname): MapperElementInterface;

  /**
   * @return array
   */
  public function getFields(): array;

  /**
   * @return $this
   */
  public function setIsView(bool $isView): MapperElementInterface;

  /**
   * @return bool
   */
  public function getIsView(): bool;


}
