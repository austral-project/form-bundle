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
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Mapper\GroupFields;
use Austral\FormBundle\Mapper\Modal;

/**
 * Austral Mapper Element Interface.
 * @template T of object
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface MapperElementInterface
{

  /**
   * @return MapperElementInterface<T>|null
   */
  public function parent(): ?MapperElementInterface;

  /**
   * @return MapperElementInterface<T>
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
   * setKeyname
   *
   * @param string $keyname
   * @return $this
   */
  public function setKeyname(string $keyname): self;

  /**
   * @return FormMapper<T>|null
   */
  public function formMapper(): ?FormMapper;

  /**
   * @return bool
   */
  public function isInModal(): bool;

  /**
   * @param string $name
   * @param string|null $fieldname
   * @param array<string, mixed> $attr
   *
   * @return Modal<T>|null
   * @throws \Exception
   */
  public function addModal(string $name, ?string $fieldname = null, array $attr = []): ?Modal;

  /**
   * @param FieldInterface<object>|FieldInterface<T>|null $field
   * @param int|null $sortable
   *
   * @return $this
   * @throws \Exception
   */
  public function add(?FieldInterface $field, ?int $sortable = null): self;

  /**
   * @param array<string, FieldInterface<T>|MapperElementInterface<T>|array{"field": mixed}> $fields
   * @return $this
   * @throws \Exception
   */
  public function adds(array $fields): self;

  /**
   * @param string $name
   * @param string|null $entitled
   *
   * @return GroupFields<T>
   */
  public function addGroup(string $name, ?string $entitled = null): GroupFields;

  /**
   * @param FieldInterface<object>|FieldInterface<T> $field
   *
   * @return $this
   */
  public function configField(FieldInterface $field): self;

  /**
   * @param FieldInterface<object>|FieldInterface<T> $field
   *
   * @return $this
   */
  public function addAllFields(FieldInterface $field): self;

  /**
   * @return array<string, FieldInterface<object>|FieldInterface<T>|MapperElementInterface<T>>
   */
  public function allFields(): array;

  /**
   * @param string $fieldname
   *
   * @return $this
   */
  public function removeAllField(string $fieldname): self;

  /**
   * @return array<string, FieldInterface<object>|FieldInterface<T>|MapperElementInterface<T>>
   */
  public function getFields(): array;

  /**
   * @param string $fieldname
   *
   * @return FieldInterface<object>|FieldInterface<T>|MapperElementInterface<T>|null
   */
  public function getField(string $fieldname): FieldInterface|MapperElementInterface|null;

  /**
   * @param string $fieldMapping
   *
   * @return $this
   */
  public function addFieldsMapping(string $fieldMapping): self;

  /**
   * @return array<string, FieldInterface<T>|MapperElementInterface<T>>
   */
  public function getFieldsSortable(): array;

  /**
   * @return $this
   */
  public function setIsView(bool $isView): self;

  /**
   * @return bool
   */
  public function getIsView(): bool;

  /**
   * @return int|null
   */
  public function getSortable(): ?int;

  /**
   * @param int|null $sortable
   *
   * @return $this
   */
  public function setSortable(?int $sortable = null): self;

  /**
   * @return array<string, mixed>
   */
  public function getAttr(): array;

  /**
   * @param array<string, mixed> $attr
   *
   * @return $this
   */
  public function setAttr(array $attr): self;

  /**
   * @return string|null
   */
  public function classCss(): ?string;

  /**
   * @return string|null
   */
  public function attrString(): ?string;


}
