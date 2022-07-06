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
use Austral\FormBundle\Mapper\Popin;

/**
 * Austral Mapper Element.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
abstract class MapperElement implements MapperElementInterface
{

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
   * @var array
   */
  protected array $fields = array();

  /**
   * @var MapperElementInterface
   */
  protected MapperElementInterface $parent;

  /**
   * @return MapperElementInterface
   */
  public function parent(): MapperElementInterface
  {
    return $this->parent;
  }

  /**
   * @return FormMapper
   */
  public function formMapper(): FormMapper
  {
    if(!$this->parent instanceof FormMapper)
    {
      return $this->parent->formMapper();
    }
    return $this->parent;
  }

  /**
   * @return bool
   */
  public function isInPopin(): bool
  {
    if($this instanceof Popin)
    {
      return true;
    }
    elseif(!$this->parent instanceof FormMapper)
    {
      return $this->parent->isInPopin();
    }
    return false;
  }

  /**
   * @param string $name
   * @param string|null $fielname
   * @param array $attr
   *
   * @return Popin
   * @throws \Exception
   */
  public function addPopin(string $name, string $fielname = null, array $attr = array()): Popin
  {
    return $this->formMapper()->addPopin($name, $fielname, $attr, $this);
  }

  /**
   * @return MapperElementInterface
   */
  public function end(): MapperElementInterface
  {
    return $this->parent;
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
   * @return string
   */
  public function getFieldname(): string
  {
    return $this->keyname;
  }

  /**
   * @param FieldInterface|null $field
   *
   * @return MapperElement
   */
  public function add(?FieldInterface $field): MapperElement
  {
    if($field && $this->isView)
    {
      $field->setIsInPopin($this->isInPopin());
      if($field->getIsView() && $this->isView && !array_key_exists($field->getFieldname(), $this->formMapper()->allFields()))
      {
        $this->configField($field)->addAllFields($field);
        $this->fields[$field->getFieldname()] = $field;
        $this->uploadFieldEditor($field);
      }
    }
    return $this;
  }

  /**
   * @param string $name
   * @param string|null $entitled
   *
   * @return GroupFields
   */
  public function addGroup(string $name, string $entitled = null): GroupFields
  {
    $blockFields = new GroupFields($name, $this, $entitled);
    if(!array_key_exists($blockFields->getKeyname(), $this->fields)) {

      $this->fields[$blockFields->getKeyname()] = $blockFields;
    }
    else {
      $blockFields = $this->fields[$blockFields->getKeyname()];
    }
    return $blockFields;
  }

  /**
   * @var array $fields
   * @return $this
   * @throws \Exception
   */
  public function adds(array $fields): MapperElement
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
   * @param FieldInterface $field
   */
  protected function uploadFieldEditor(FieldInterface $field)
  {
    if($field instanceof UploadField && $field->getEditorFields())
    {
      /** @var FieldInterface $editorField */
      foreach($field->getEditorFields() as $editorField)
      {
        $this->configField($editorField)->addAllFields($field);
      }
    }
  }

  /**
   * @param string $fieldname
   *
   * @return $this
   */
  public function removeField(string $fieldname): MapperElement
  {
    if(array_key_exists($fieldname, $this->fields))
    {
      unset($this->fields[$fieldname]);
      $this->removeAllField($fieldname);
    }
    return $this;
  }

  /**
   * @param FieldInterface $field
   *
   * @return MapperElement
   */
  public function configField(FieldInterface $field): MapperElement
  {
    $this->parent()->configField($field);
    return $this;
  }

  /**
   * @param string $fieldname
   *
   * @return FieldInterface|null
   */
  public function getField(string $fieldname): ?FieldInterface
  {
    return $this->parent()->getField($fieldname);
  }

  /**
   * @param FieldInterface $field
   *
   * @return MapperElement
   */
  public function addAllFields(FieldInterface $field): MapperElement
  {
    $this->formMapper()->addAllFields($field);
    return $this;
  }

  /**
   * @param string $fieldname
   *
   * @return MapperElement
   */
  public function removeAllField(string $fieldname): MapperElement
  {
    $this->parent()->removeAllField($fieldname);
    return $this;
  }

  /**
   * @param string $fieldMapping
   *
   * @return MapperElement
   */
  public function addFieldsMapping(string $fieldMapping): MapperElement
  {
    $this->parent()->addFieldsMapping($fieldMapping);
    return $this;
  }

  /**
   * @return array
   */
  public function getFields(): array
  {
    return $this->fields;
  }

  /**
   * @param bool $isView
   *
   * @return MapperElement
   */
  public function setIsView(bool $isView): MapperElement
  {
    $this->isView = $isView;
    return $this;
  }

  /**
   * @return bool
   */
  public function getIsView(): bool
  {
    return $this->isView;
  }


}