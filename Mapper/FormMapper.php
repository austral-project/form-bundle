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

use Austral\AdminBundle\Module\Module;
use Austral\FormBundle\Mapper\Base\MapperElement;

use Austral\FormBundle\Event\FormFieldEvent;
use Austral\FormBundle\Field\Base\FieldInterface;

use Austral\EntityBundle\Entity\EntityInterface;

use Austral\FormBundle\Mapper\Base\MapperElementInterface;
use Austral\ListBundle\Column\Interfaces\ColumnActionInterface;
use Austral\ToolsBundle\AustralTools;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Form Mapper.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class FormMapper extends MapperElement implements MapperElementInterface
{

  /**
   * @var EventDispatcherInterface|null
   */
  protected ?EventDispatcherInterface $dispatcher;

  /**
   * @var EntityInterface|null
   */
  protected ?EntityInterface $object = null;

  /**
   * @var string
   */
  protected string $name = "form_master";

  /**
   * @var array
   */
  protected array $objects = array();

  /** @var ?FormMapper */
  protected ?FormMapper $parentFormMapper = null;

  /**
   * @var array
   */
  protected array $subFormMappers = array();

  /**
   * @var array
   */
  protected array $fieldsMapping = array();

  /**
   * @var array
   */
  protected array $allFields = array();

  /**
   * @var string|null
   */
  protected ?string $pathToTemplateDefault = null;

  /**
   * @var array
   */
  protected array $fieldsets = array();

  /**
   * @var array
   */
  protected array $popins = array();

  /**
   * @var array
   */
  protected array $actions = array();

  /**
   * @var array
   */
  protected array $popinKeysByFieldname = array();

  /**
   * @var array
   */
  protected array $uniqueFields = array();

  /**
   * @var array
   */
  protected array $options = array();

  /**
   * @var array
   */
  protected array $attributes = array(
    "class" =>  ""
  );

  /**
   * @var string|null
   */
  protected ?string $formTypeAction = null;

  /**
   * @var string|null
   */
  protected ?string $formStatus = null;

  /**
   * @var Module|null
   */
  protected ?Module $module = null;

  /**
   * @var bool
   */
  protected bool $formSend = false;

  /**
   * @var string
   */
  protected string $requestMethod = "GET";

  /**
   * Mapper constructor.
   *
   */
  public function __construct(EventDispatcherInterface $dispatcher = null)
  {
    $this->dispatcher = $dispatcher;
    $this->options = array(
      "translation_domain" => "form",
      "html5_validate" => false
    );
    $this->isView = true;
  }

  /**
   * @return $this
   */
  public function reinit(): FormMapper
  {
    $this->fieldsMapping = array();
    $this->allFields = array();
    $this->fieldsets = array();
    return $this;
  }

  /**
   * @return array
   */
  public function getAttributes(): array
  {
    return $this->attributes;
  }

  /**
   * @param array $attributes
   *
   * @return FormMapper
   */
  public function setAttributes(array $attributes): FormMapper
  {
    $this->attributes = $attributes;
    return $this;
  }

  /**
   * @param string $key
   * @param $value
   *
   * @return FormMapper
   */
  public function setAttribute(string $key, $value): FormMapper
  {
    $this->attributes[$key] = $value;
    return $this;
  }

  /**
   * @param string $value
   *
   * @return $this
   */
  public function setTranslateDomain(string $value): FormMapper
  {
    $this->options['translation_domain'] = $value;
    if($this->subFormMappers)
    {
      /** @var FormMapper $subFormMapper */
      foreach($this->subFormMappers as $subFormMapper)
      {
        $subFormMapper->setTranslateDomain($value);
      }
    }
    return $this;
  }

  /**
   * @return string
   */
  public function getTranslateDomain(): string
  {
    return $this->options['translation_domain'];
  }

  /**
   * @param EventDispatcherInterface|null $dispatcher
   *
   * @return $this
   */
  public function setDispatcher(EventDispatcherInterface $dispatcher = null): FormMapper
  {
    $this->dispatcher = $dispatcher;
    if($this->subFormMappers)
    {
      /** @var FormMapper $subFormMapper */
      foreach($this->subFormMappers as $subFormMapper)
      {
        $subFormMapper->setDispatcher($this->dispatcher);
      }
    }
    return $this;
  }

  /**
   * @return EventDispatcherInterface|null
   */
  public function getDispatcher(): ?EventDispatcherInterface
  {
    return $this->dispatcher;
  }

  /**
   * @return ?EntityInterface $object
   */
  public function getObject(): ?EntityInterface
  {
    return $this->object;
  }

  /**
   * @param EntityInterface $object
   *
   * @return $this
   */
  public function setObject(EntityInterface $object): FormMapper
  {
    $this->object = $object;
    return $this;
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
  public function getkeyname(): string
  {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return FormMapper
   */
  public function setName(string $name): FormMapper
  {
    $this->name = $name;
    return $this;
  }

  /**
   * @param array $objects
   *
   * @return $this
   */
  public function setObjects(array $objects): FormMapper
  {
    $this->objects = $objects;
    return $this;
  }

  /**
   * Get fieldsMapping
   * @return array
   */
  public function getFieldsMapping(): array
  {
    return $this->fieldsMapping;
  }

  /**
   * @param array $fieldsMapping
   *
   * @return FormMapper
   */
  public function setFieldsMapping(array $fieldsMapping): FormMapper
  {
    $this->fieldsMapping = $fieldsMapping;
    return $this;
  }

  /**
   * @param string $fieldMapping
   *
   * @return FormMapper
   */
  public function addFieldsMapping(string $fieldMapping): FormMapper
  {
    $this->fieldsMapping[$fieldMapping] = $fieldMapping;
    return $this;
  }

  /**
   * @param string|null $formTypeAction
   *
   * @return FormMapper
   */
  public function setFormTypeAction(?string $formTypeAction): FormMapper
  {
    $this->formTypeAction = $formTypeAction;
    return $this;
  }

  /**
   * Get formTypeAction
   * @return string|null
   */
  public function getFormTypeAction(): ?string
  {
    return $this->formTypeAction;
  }

  /**
   * Get formStatus
   * @return string|null
   */
  public function getFormStatus(): ?string
  {
    return $this->formStatus;
  }

  /**
   * @param string|null $formStatus
   *
   * @return FormMapper
   */
  public function setFormStatus(?string $formStatus): FormMapper
  {
    $this->formStatus = $formStatus;
    return $this;
  }

  /**
   * Get formSend
   * @return bool
   */
  public function getFormSend(): bool
  {
    return $this->formSend;
  }

  /**
   * @param bool $formSend
   *
   * @return FormMapper
   */
  public function setFormSend(bool $formSend): FormMapper
  {
    $this->formSend = $formSend;
    return $this;
  }

  /**
   * @return string
   */
  public function getObjectSluggerClassname(): string
  {
    return $this->object->getSluggerClassname();
  }

  /**
   * @param string $key
   * @param null $default
   *
   * @return array|mixed|string|null
   */
  public function getOption(string $key, $default = null)
  {
    return AustralTools::getValueByKey($this->options, $key, $default);
  }

  /**
   * Get options
   * @return array
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * @param $options
   *
   * @return $this
   */
  public function setOptions($options): FormMapper
  {
    $this->options = array_merge($this->options, $options);
    return $this;
  }

  /**
   * @param FieldInterface $field
   *
   * @return FormMapper
   * @throws \Exception
   */
  public function configField(FieldInterface $field): FormMapper
  {
    if($this->dispatcher)
    {
      $this->dispatcher->dispatch(new FormFieldEvent($this, $field), FormFieldEvent::EVENT_AUSTRAL_FIELD_CONFIGURATION);
    }
    return $this;
  }

  /**
   * @param string $fieldname
   *
   * @return FieldInterface|null
   */
  public function getField(string $fieldname): ?FieldInterface
  {
    return AustralTools::getValueByKey($this->allFields, $fieldname, null);
  }

  /**
   * @return $this
   */
  public function parent() : FormMapper
  {
    return $this;
  }

  /**
   * @param FieldInterface|null $field
   * @param int|null $sortable
   *
   * @return $this
   */
  public function add(?FieldInterface $field, int $sortable = null): FormMapper
  {
    if($field)
    {
      $fieldset = $this->addFieldset("no-fieldset")
        ->setPositionName(Fieldset::POSITION_BOTTOM)
        ->setIsView(true)
        ->setSortable($sortable)
        ->setViewName(false);
      $fieldset->add($field);
    }
    return $this;
  }

  /**
   * @param string $name
   * @param string|null $entitled
   *
   * @return Fieldset
   */
  public function addFieldset(string $name, ?string $entitled = null): Fieldset
  {
    $fieldset = new Fieldset($name, $this, $entitled);
    if(!array_key_exists($fieldset->getKeyname(), $this->fieldsets)) {
      $this->fields[$fieldset->getKeyname()] = $fieldset;
      $this->fieldsets[$fieldset->getKeyname()] = $fieldset;
    }
    else {
      $fieldset = $this->fieldsets[$fieldset->getKeyname()];
    }
    return $fieldset;
  }

  /**
   * @param string $positionName
   *
   * @return array
   */
  public function getFieldsetByPosition(string $positionName): array
  {
    $fieldsets = array();
    $count = 0;
    /** @var Fieldset $fieldset */
    foreach($this->getFieldsets() as $fieldset)
    {
      if($fieldset->getIsView()) {
        if($fieldset->getPositionName() == $positionName)
        {
          $sortable = $fieldset->getSortable() !== null ? $fieldset->getSortable() : $count;
          $fieldsets["{$sortable}-{$fieldset->getKeyname()}"] = $fieldset;
          $count++;
        }
      }
    }
    ksort($fieldsets, SORT_NUMERIC);
    return $fieldsets;
  }

  /**
   * @param string $name
   * @param string|null $fieldname
   * @param array $attr
   * @param MapperElementInterface|null $parent
   *
   * @return Popin
   * @throws \Exception
   */
  public function addPopin(string $name, string $fieldname = null, array $attr = array(), MapperElementInterface $parent = null): Popin
  {
    $popin = new Popin($name, $parent ?? $this, $fieldname, $attr);
    if(!array_key_exists($popin->getKeyname(), $this->fields)) {
      $this->popins[$popin->getKeyname()] = $popin;
      if($fieldname)
      {
        $this->popinKeysByFieldname[$fieldname] = $popin->getKeyname();
        /** @var FieldInterface $field */
        if(array_key_exists($fieldname, $this->allFields))
        {
          $this->allFields[$fieldname]->setPopinId($popin->getKeyname());
        }
      }
    }
    else {
      $popin = $this->popins[$popin->getKeyname()];
    }
    return $popin;
  }

  /**
   * @return array
   */
  public function fields(): array
  {
    return $this->fields;
  }

  /**
   * @param string $key
   *
   * @return array|mixed|string|null
   */
  public function fieldsByKey(string $key)
  {
    return AustralTools::getValueByKey($this->fields(), $key, array());
  }

  /**
   * @return array
   */
  public function allFields(): array
  {
    return $this->allFields;
  }

  /**
   * @return array
   */
  public function getFieldsets(): array
  {
    return $this->fieldsets;
  }

  /**
   * @return array
   */
  public function getPopins(): array
  {
    return $this->popins;
  }

  /**
   * @param string $key
   *
   * @return array|Popin
   */
  public function getPopinByKey(string $key)
  {
    return AustralTools::getValueByKey($this->popins, $key, array());
  }

  /**
   * @param FieldInterface $column
   *
   * @return $this
   */
  public function addAllFields(FieldInterface $column): FormMapper
  {
    $column->setFormMapper($this);
    $this->allFields[$column->getFieldname()] = $column;
    /** @var FieldInterface $field */
    if(array_key_exists($column->getFieldname(), $this->popinKeysByFieldname))
    {
      $this->allFields[$column->getFieldname()]->setPopinId($this->popinKeysByFieldname[$column->getFieldname()]);
    }
    return $this;
  }

  /**
   * @param string $fieldname
   *
   * @return $this
   */
  public function removeAllField(string $fieldname): FormMapper
  {
    if(array_key_exists($fieldname, $this->allFields))
    {
      unset($this->allFields[$fieldname]);
    }
    /** @var Fieldset $fieldset */
    foreach ($this->fieldsets as $fieldset)
    {
      $fieldset->removeField($fieldname);
    }
    return $this;
  }

  /**
   * @return array
   */
  public function getUniqueFields(): array
  {
    return $this->uniqueFields;
  }

  /**
   * @param string $fieldname
   *
   * @return $this
   */
  public function addUniqueField(string $fieldname): FormMapper
  {
    $this->uniqueFields[] = $fieldname;
    return $this;
  }

  /**
   * @return array
   */
  public function getSubFormMappers(): array
  {
    return $this->subFormMappers;
  }

  /**
   * @param string $subFormMapperKey
   * @param null $default
   *
   * @return FormMapper|null
   */
  public function getSubFormMapperByKey(string $subFormMapperKey, $default = null): ?FormMapper
  {
    return AustralTools::getValueByKey($this->subFormMappers, $subFormMapperKey, $default);
  }

  /**
   * @param string $subFormMapperKey
   *
   * @return bool
   */
  public function hasSubFormMapperByKey(string $subFormMapperKey): bool
  {
    return array_key_exists($subFormMapperKey, $this->subFormMappers);
  }

  /**
   * @param array $subFormMappers
   *
   * @return $this
   */
  public function setSubFormMappers(array $subFormMappers): FormMapper
  {
    $this->subFormMappers = $subFormMappers;
    return $this;
  }

  /**
   * @param string $subFormMapperKey
   * @param FormMapper $subFormMapper
   * @param bool $addDispatcher
   *
   * @return $this
   */
  public function addSubFormMapper(string $subFormMapperKey, FormMapper $subFormMapper, bool $addDispatcher = true): FormMapper
  {
    if($addDispatcher)
    {
      $subFormMapper->setDispatcher($this->dispatcher);
      if($subFormMapper->getSubFormMappers())
      {
        /** @var FormMapper $subFormMapperChild */
        foreach ($subFormMapper->getSubFormMappers() as $subFormMapperChild)
        {
          $subFormMapperChild->setDispatcher($this->dispatcher);
          $subFormMapperChild->setTranslateDomain($this->getTranslateDomain());
        }
      }
    }
    $subFormMapper->setPathToTemplateDefault($this->pathToTemplateDefault);
    $subFormMapper->setTranslateDomain($this->getTranslateDomain());
    $subFormMapper->setName($subFormMapperKey);
    $subFormMapper->setParentFormMapper($this);
    $this->subFormMappers[$subFormMapperKey] = $subFormMapper;
    return $this;
  }

  /**
   * @return FormMapper|null
   */
  public function getParentFormMapper(): ?FormMapper
  {
    return $this->parentFormMapper;
  }

  /**
   * @return bool
   */
  public function hasParentFormMapper(): bool
  {
    return (bool) $this->parentFormMapper;
  }

  /**
   * @param FormMapper|null $parentFormMapper
   *
   * @return FormMapper
   */
  public function setParentFormMapper(?FormMapper $parentFormMapper): FormMapper
  {
    $this->parentFormMapper = $parentFormMapper;
    return $this;
  }

  /**
   * @return array
   */
  public function getAllSubFormMapper(): array
  {
    $allSubMappers = array();
    foreach($this->getSubFormMappers() as $subFormMapper)
    {
      $allSubMappers[$subFormMapper->getName()] = $subFormMapper;
      foreach ($subFormMapper->getAllSubFormMapper() as $test)
      {
        $allSubMappers[$test->getName()] = $test;
      }
    }
    return $allSubMappers;
  }

  /**
   * @return string|null
   */
  public function getPathToTemplateDefault(): ?string
  {
    return $this->pathToTemplateDefault;
  }

  /**
   * @param string|null $pathToTemplateDefault
   *
   * @return FormMapper
   */
  public function setPathToTemplateDefault(?string $pathToTemplateDefault): FormMapper
  {
    $this->pathToTemplateDefault = $pathToTemplateDefault;
    if($this->subFormMappers)
    {
      /** @var FormMapper $subFormMapper */
      foreach($this->subFormMappers as $subFormMapper)
      {
        $subFormMapper->setPathToTemplateDefault($this->pathToTemplateDefault);
      }
    }
    return $this;
  }

  /**
   * @param ColumnActionInterface $action
   * @param int|null $position
   *
   * @return $this
   */
  public function addAction(ColumnActionInterface $action, ?int $position = null): FormMapper
  {
    if(!$action->translateDomain())
    {
      $action->setTranslateDomain($this->getTranslateDomain());
    }
    $position = $position > 0 ? $position : count($this->actions)+1;
    $this->actions["{$position}-{$action->keyname()}"] = $action;
    ksort($this->actions);
    return $this;
  }

  /**
   * @return string
   */
  public function getRequestMethod(): string
  {
    return $this->requestMethod;
  }

  /**
   * @param string $requestMethod
   *
   * @return FormMapper
   */
  public function setRequestMethod(string $requestMethod): FormMapper
  {
    $this->requestMethod = $requestMethod;
    return $this;
  }

  /**
   * @return array
   */
  public function actions(): array
  {
    return $this->actions;
  }

  /**
   * @return Module|null
   */
  public function getModule(): ?Module
  {
    return $this->module;
  }

  /**
   * @param Module|null $module
   *
   * @return $this
   */
  public function setModule(?Module $module): FormMapper
  {
    $this->module = $module;
    return $this;
  }

}