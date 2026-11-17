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
use Austral\FormBundle\Field\UploadField;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Mapper\GroupFields;
use Austral\FormBundle\Mapper\Modal;
use Austral\ToolsBundle\AustralTools;
use Random\RandomException;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Austral Mapper Element.
 * @template T of object
 *
 * @implements MapperElementInterface<T>
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
abstract class MapperElement implements MapperElementInterface
{

  /**
   * @var string
   */
  public static string $keynameBefore = "";

  /**
   * @var string
   */
  public static string $keynameAfter = "";

  /**
   * @var bool
   */
  protected bool $isView = true;

  /**
   * @var string
   */
  protected string $name;

  /**
   * @var string
   */
  protected string $keyname;

  /**
   * @var array<string, FieldInterface<object>|FieldInterface<T>|MapperElementInterface<T>>
   */
  protected array $fields = [];

  /**
   * @var array<string, int>
   */
  protected array $fieldKeynameByPositions = [];

  /**
   * @var int|null
   */
  protected ?int $sortable = null;

  /**
   * @var array<string, mixed>
   */
  protected array $attr = [];

  /**
   * @var MapperElementInterface<T>|null
   */
  protected ?MapperElementInterface $parent = null;

  /**
   * MapperElement constructor
   *
   * @param string $name
   * @param MapperElementInterface<T>|null $parent
   */
  public function __construct(string $name, ?MapperElementInterface $parent = null)
  {
    $this->parent = $parent;
    $this->name = $name;
    $this->setKeyname($name);
  }

  /**
   * @inheritDoc
   */
  public function parent(): ?MapperElementInterface
  {
    return $this->parent;
  }

  /**
   * @inheritDoc
   */
  public function formMapper(): ?FormMapper
  {
    if($this instanceof FormMapper)
    {
      return $this;
    }
    return $this->parent?->formMapper();
  }

  /**
   * @inheritDoc
   */
  public function isInModal(): bool
  {
    if($this instanceof Modal)
    {
      return true;
    }
    elseif($this->parent && !$this->parent instanceof FormMapper)
    {
      return $this->parent->isInModal();
    }
    return false;
  }

  /**
   * @inheritDoc
   */
  public function addModal(string $name, ?string $fieldname = null, array $attr = []): ?Modal
  {
    return $this->formMapper()?->addModal($name, $fieldname, $attr, $this);
  }

  /**
   * @inheritDoc
   */
  public function end(): MapperElementInterface
  {
    return $this->parent ?? $this;
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @return string
   */
  public function getKeyname(): string
  {
    return $this->keyname;
  }

  /**
   * setKeyname
   *
   * @param string $keyname
   * @return $this
   */
  public function setKeyname(string $keyname): self
  {
    $slugger = new AsciiSlugger();
    $this->keyname = self::$keynameBefore.strtolower($slugger->slug($keyname)).self::$keynameAfter;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function add(?FieldInterface $field, ?int $sortable = null): self
  {
    if($field && $this->isView)
    {
      $field->setIsInModal($this->isInModal());
      if($field->getIsView())
      {
        if(array_key_exists($field->getFieldname(), $this->allFields()))
        {
          $this->formMapper()?->removeAllField($field->getFieldname());
        }
        if($this->isView !== false)
        {
          $this->configField($field)->addAllFields($field);
          $this->fields[$field->getFieldname()] = $field;
          $this->uploadFieldEditor($field);
          if($sortable)
          {
            $this->fieldKeynameByPositions[$field->getFieldname()] = $sortable;
          }
        }
      }
    }
    return $this;
  }

  /**
   * @param string $name
   * @param string|null $entitled
   *
   * @return GroupFields<T>
   * @throws RandomException
   */
  public function addGroup(string $name, ?string $entitled = null): GroupFields
  {
    $blockFields = new GroupFields($name, $this, $entitled);
    $create = true;
    if(array_key_exists($blockFields->getKeyname(), $this->fields)) {
      $blockFieldExists = $this->fields[$blockFields->getKeyname()];
      if($blockFieldExists instanceof GroupFields)
      {
        $blockFields = $blockFieldExists;
        $create = false;
      }
      else
      {
        $blockFields->setKeyname($name."_".AustralTools::random(4));
      }
    }
    if($create)
    {
      $this->fields[$blockFields->getKeyname()] = $blockFields;
    }
    return $blockFields;
  }

  /**
   * @inheritDoc
   */
  public function adds(array $fields): self
  {
    foreach ($fields as $field)
    {
      if($field instanceof FieldInterface)
      {
        $this->add($field);
      }
      elseif(is_array($field))
      {
        $this->add($field["field"]);
      }
    }
    return $this;
  }


  /**
   * @param FieldInterface<object>|FieldInterface<T> $field
   */
  protected function uploadFieldEditor(FieldInterface $field): void
  {
    if($field instanceof UploadField && $field->getEditorFields())
    {
      /** @var FieldInterface<T> $editorField */
      foreach($field->getEditorFields() as $editorField)
      {
        $this->configField($editorField)->addAllFields($field);
      }
    }
  }

  /**
   * @param string $fieldname
   *
   * @return FieldInterface<object>|FieldInterface<T>|MapperElementInterface<T>|null
   */
  public function getField(string $fieldname): FieldInterface|MapperElementInterface|null
  {
    return array_key_exists($fieldname, $this->fields) ? $this->fields[$fieldname] : null;
  }

  /**
   * @param string $fieldname
   *
   * @return $this
   */
  public function removeField(string $fieldname): self
  {
    if(array_key_exists($fieldname, $this->fields))
    {
      unset($this->fields[$fieldname]);
      $this->removeAllField($fieldname);
    }
    foreach ($this->fields as $field)
    {
      if($field instanceof GroupFields)
      {
        $field->removeField($fieldname);
      }
    }
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function configField(FieldInterface $field): self
  {
    $this->parent()?->configField($field);
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function addAllFields(FieldInterface $field): self
  {
    $this->formMapper()?->addAllFields($field);
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function allFields(): array
  {
    $fields = $this->formMapper()?->allFields();
    if(!$fields)
    {
      $fields = [];
    }
    return $fields;
  }

  /**
   * @inheritDoc
   */
  public function removeAllField(string $fieldname): self
  {
    $this->parent()?->removeAllField($fieldname);
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function addFieldsMapping(string $fieldMapping): self
  {
    $this->parent()?->addFieldsMapping($fieldMapping);
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getFields(): array
  {
    return $this->fields;
  }

  /**
   * @inheritDoc
   */
  public function getFieldsSortable(): array
  {
    $fields = [];
    $count = 0;
    /** @var FieldInterface<T>|GroupFields<T> $field */
    foreach($this->fields as $field)
    {
      if($field instanceof FieldInterface)
      {
        $sortable = AustralTools::valueByKey($this->fieldKeynameByPositions, $field->getFieldname(), $count);
        $keySortable = "{$sortable}-{$field->getFieldname()}";
      }
      else
      {
        $sortable = $field->getSortable() !== null ? $field->getSortable() : $count;
        $keySortable = "{$sortable}-{$field->getKeyname()}";
      }
      $fields[$keySortable] = $field;
      $count++;
    }
    ksort($fields, SORT_NUMERIC);
    return $fields;
  }

  /**
   * @inheritDoc
   */
  public function setIsView(bool $isView): self
  {
    $this->isView = $isView;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getIsView(): bool
  {
    return $this->isView;
  }

  /**
   * @inheritDoc
   */
  public function getSortable(): ?int
  {
    return $this->sortable;
  }

  /**
   * @inheritDoc
   */
  public function setSortable(?int $sortable = null): self
  {
    $this->sortable = $sortable;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getAttr(): array
  {
    return $this->attr;
  }

  /**
   * @inheritDoc
   */
  public function setAttr(array $attr): self
  {
    $this->attr = $attr;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function classCss(): ?string
  {
    return AustralTools::valueByKey($this->attr, "class", null);
  }

  /**
   * @inheritDoc
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
   * @param array<string, mixed> $array
   *
   * @return string
   */
  protected function stringifyArray(string $value = "", array $array = []): string
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


}