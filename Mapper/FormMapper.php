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

use Austral\FormBundle\Mapper\Base\MapperElement;

use Austral\FormBundle\Event\FormFieldEvent;
use Austral\FormBundle\Field\Base\FieldInterface;

use Austral\Contracts\Entity\EntityInterface;

use Austral\FormBundle\Mapper\Base\MapperElementInterface;
use Austral\ToolsBundle\AustralTools;

use Random\RandomException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Form Mapper.
 * @template T of object
 *
 * @extends MapperElement<T>
 * @implements MapperElementInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class FormMapper extends MapperElement implements MapperElementInterface
{

  /**
   * @var EntityInterface<T>|null
   */
  protected ?EntityInterface $object = null;

  /**
   * @var string
   */
  protected string $name = "form_master";

  /**
   * @var array<string|int, EntityInterface<T>>
   */
  protected array $objects = [];

  /**
   * @var ?FormMapper<T>
   */
  protected ?FormMapper $parentFormMapper = null;

  /**
   * @var array<string, FormMapper<T>>
   */
  protected array $subFormMappers = [];

  /**
   * @var array<string, string>
   */
  protected array $fieldsMapping = [];

  /**
   * @var array<string, FieldInterface<object>|FieldInterface<T>>
   */
  protected array $allFields = [];

  /**
   * @var string|null
   */
  protected ?string $pathToTemplateDefault = null;

  /**
   * @var array<string, Fieldset<T>>
   */
  protected array $fieldsets = [];

  /**
   * @var array<string, Modal<T>>
   */
  protected array $modals = [];


  /**
   * @var array<string, string>
   */
  protected array $modalKeysByFieldname = [];

  /**
   * @var array<int, string>
   */
  protected array $uniqueFields = [];

  /**
   * @var array<string, mixed>
   */
  protected array $options = [];

  /**
   * @var string|null
   */
  protected ?string $formTypeAction = null;

  /**
   * @var string|null
   */
  protected ?string $formStatus = null;

  /**
   * @var bool
   */
  protected bool $formSend = false;

  /**
   * @var string
   */
  protected string $requestMethod = "GET";

  /**
   * FormMapper constructor
   *
   * @param EventDispatcherInterface|null $dispatcher
   * @param string $name
   */
  public function __construct(
    #[Autowire(service: 'event_dispatcher')] protected ?EventDispatcherInterface $dispatcher = null,
    string $name = "form_master")
  {
    parent::__construct($name, null);
    $this->options = array(
      "translation_domain" => "form",
      "html5_validate" => false
    );
    $this->isView = true;
  }

  /**
   * @return $this
   */
  public function reinit(): self
  {
    $this->fieldsMapping = [];
    $this->allFields = [];
    $this->fieldsets = [];
    return $this;
  }

  /**
   * @param string $value
   *
   * @return $this
   */
  public function setTranslateDomain(string $value): self
  {
    $this->options['translation_domain'] = $value;
    if($this->subFormMappers)
    {
      /** @var FormMapper<T> $subFormMapper */
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
   * @param EventDispatcherInterface $dispatcher
   *
   * @return $this
   */
  public function setDispatcher(EventDispatcherInterface $dispatcher): self
  {
    $this->dispatcher = $dispatcher;
    if($this->subFormMappers)
    {
      /** @var FormMapper<T> $subFormMapper */
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
   * @return ?EntityInterface<T> $object
   */
  public function getObject(): ?EntityInterface
  {
    return $this->object;
  }

  /**
   * @param EntityInterface<T> $object
   *
   * @return $this
   */
  public function setObject(EntityInterface $object): self
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
   * @param string $name
   *
   * @return $this
   */
  public function setName(string $name): self
  {
    $this->name = $name;
    $this->setKeyname($name);
    return $this;
  }

  /**
   * @param array<string|int, EntityInterface<T>> $objects
   *
   * @return $this
   */
  public function setObjects(array $objects): self
  {
    $this->objects = $objects;
    return $this;
  }

  /**
   * Get fieldsMapping
   * @return array<string, string>
   */
  public function getFieldsMapping(): array
  {
    return $this->fieldsMapping;
  }

  /**
   * @param array<string, string> $fieldsMapping
   *
   * @return $this
   */
  public function setFieldsMapping(array $fieldsMapping): self
  {
    $this->fieldsMapping = $fieldsMapping;
    return $this;
  }

  /**
   * @param string $fieldMapping
   *
   * @return $this
   */
  public function addFieldsMapping(string $fieldMapping): self
  {
    $this->fieldsMapping[$fieldMapping] = $fieldMapping;
    return $this;
  }

  /**
   * @param string|null $formTypeAction
   *
   * @return $this
   */
  public function setFormTypeAction(?string $formTypeAction): self
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
   * @return $this
   */
  public function setFormStatus(?string $formStatus): self
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
   * @return $this
   */
  public function setFormSend(bool $formSend): self
  {
    $this->formSend = $formSend;
    return $this;
  }

  /**
   * @return string
   */
  public function getObjectSluggerClassname(): string
  {
    return $this->object?->getSluggerClassname() ?? "";
  }

  /**
   * @param string $key
   * @param mixed $default
   *
   * @return mixed
   */
  public function getOption(string $key, mixed $default = null): mixed
  {
    return AustralTools::valueByKey($this->options, $key, $default);
  }

  /**
   * Get options
   * @return array<string, mixed>
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * @param array<string, mixed> $options
   *
   * @return $this
   */
  public function setOptions(array $options): FormMapper
  {
    $this->options = array_merge($this->options, $options);
    return $this;
  }

  /**
   * @param FieldInterface<T> $field
   *
   * @return $this
   * @throws \Exception
   */
  public function configField(FieldInterface $field): self
  {
    $this->dispatcher?->dispatch(new FormFieldEvent($this, $field), FormFieldEvent::EVENT_AUSTRAL_FIELD_CONFIGURATION);
    return $this;
  }

  /**
   * @param string $fieldname
   *
   * @return FieldInterface<T>|null
   */
  public function getField(string $fieldname): ?FieldInterface
  {
    return AustralTools::valueByKey($this->allFields, $fieldname);
  }

  /**
   * @return $this
   */
  public function parent() : FormMapper
  {
    return $this;
  }

  /**
   * @return $this
   */
  public function end(): FormMapper
  {
    return $this;
  }

  /**
   * @param FieldInterface<object>|FieldInterface<T>|null $field
   * @param int|null $sortable
   *
   * @return $this
   * @throws RandomException
   * @throws \Exception
   */
  public function add(?FieldInterface $field, ?int $sortable = null): FormMapper
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
   * @return Fieldset<T>
   * @throws RandomException
   */
  public function addFieldset(string $name, ?string $entitled = null): Fieldset
  {
    $blockFields = new Fieldset($name, $this, $entitled);
    $create = true;
    if(array_key_exists($blockFields->getKeyname(), $this->fields)) {
      $blockFieldExists = $this->fields[$blockFields->getKeyname()];
      if($blockFieldExists instanceof Fieldset)
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
      $this->fieldsets[$blockFields->getKeyname()] = $blockFields;
    }
    return $blockFields;
  }

  /**
   * @param string $name
   *
   * @return Fieldset<T>|null
   */
  public function getFieldset(string $name): ?Fieldset
  {
    $slugger = new AsciiSlugger();
    $keyname = Fieldset::$keynameBefore.strtolower($slugger->slug($name));
    if(array_key_exists($keyname, $this->fieldsets))
    {
      return $this->fieldsets[$keyname];
    }
    return null;
  }

  /**
   * @param string $positionName
   *
   * @return array<string|int, Fieldset<T>>
   */
  public function getFieldsetByPosition(string $positionName): array
  {
    $fieldsets = [];
    $count = 0;
    /** @var Fieldset<T> $fieldset */
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
   * @param array<string, mixed> $attr
   * @param MapperElementInterface<T>|null $parent
   *
   * @return Modal<T>
   * @throws \Exception
   */
  public function addModal(string $name, ?string $fieldname = null, array $attr = [], ?MapperElementInterface $parent = null): Modal
  {
    $modal = new Modal($name, $parent ?? $this, $fieldname, $attr);
    if(!array_key_exists($modal->getKeyname(), $this->fields)) {
      $this->modals[$modal->getKeyname()] = $modal;
      if($fieldname)
      {
        $this->modalKeysByFieldname[$fieldname] = $modal->getKeyname();
        if(array_key_exists($fieldname, $this->allFields))
        {
          $this->allFields[$fieldname]->setModalId($modal->getKeyname());
        }
      }
    }
    else {
      $modal = $this->modals[$modal->getKeyname()];
    }
    return $modal;
  }

  /**
   * @return array<string, FieldInterface<object>|FieldInterface<T>|MapperElementInterface<T>>
   */
  public function fields(): array
  {
    return $this->fields;
  }

  /**
   * @param string $key
   *
   * @return FieldInterface<object>|FieldInterface<T>|MapperElementInterface<T>|null
   */
  public function fieldsByKey(string $key): FieldInterface|MapperElementInterface|null
  {
    return AustralTools::valueByKey($this->fields(), $key);
  }

  /**
   * @return array<string, FieldInterface<object>|FieldInterface<T>|MapperElementInterface<T>>
   */
  public function allFields(): array
  {
    return $this->allFields;
  }

  /**
   * @return array<string, Fieldset<T>>
   */
  public function getFieldsets(): array
  {
    return $this->fieldsets;
  }

  /**
   * @return array<string, Modal<T>>
   */
  public function getModals(): array
  {
    return $this->modals;
  }

  /**
   * @param string $key
   *
   * @return Modal<T>|null
   */
  public function getModalByKey(string $key): ?Modal
  {
    return AustralTools::valueByKey($this->modals, $key);
  }

  /**
   * @param FieldInterface<object>|FieldInterface<T> $field
   *
   * @return $this
   */
  public function addAllFields(FieldInterface $field): self
  {
    $field->setFormMapper($this);
    $this->allFields[$field->getFieldname()] = $field;
    if(array_key_exists($field->getFieldname(), $this->modalKeysByFieldname))
    {
      $this->allFields[$field->getFieldname()]->setModalId($this->modalKeysByFieldname[$field->getFieldname()]);
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
    /** @var Fieldset<T> $fieldset */
    foreach ($this->fieldsets as $fieldset)
    {
      $fieldset->removeField($fieldname);
    }
    return $this;
  }

  /**
   * @return array<int, string>
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
   * @return array<string, FormMapper<T>>
   */
  public function getSubFormMappers(): array
  {
    return $this->subFormMappers;
  }

  /**
   * @param string $subFormMapperKey
   * @param null $default
   *
   * @return FormMapper<T>|null
   */
  public function getSubFormMapperByKey(string $subFormMapperKey, $default = null): ?FormMapper
  {
    return AustralTools::valueByKey($this->subFormMappers, $subFormMapperKey, $default);
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
   * @param array<string, FormMapper<T>> $subFormMappers
   *
   * @return $this
   */
  public function setSubFormMappers(array $subFormMappers): self
  {
    $this->subFormMappers = $subFormMappers;
    return $this;
  }

  /**
   * @param string $subFormMapperKey
   * @param FormMapper<T> $subFormMapper
   * @param bool $addDispatcher
   *
   * @return $this
   */
  public function addSubFormMapper(string $subFormMapperKey, FormMapper $subFormMapper, bool $addDispatcher = true): self
  {
    if($addDispatcher && $this->dispatcher)
    {
      $subFormMapper->setDispatcher($this->dispatcher);
    }
    $subFormMapper->setPathToTemplateDefault($this->pathToTemplateDefault);
    $subFormMapper->setTranslateDomain($this->getTranslateDomain());
    $subFormMapper->setName($subFormMapperKey);
    $subFormMapper->setParentFormMapper($this);
    $this->subFormMappers[$subFormMapperKey] = $subFormMapper;
    return $this;
  }

  /**
   * @return FormMapper<T>|null
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
   * @param FormMapper<T>|null $parentFormMapper
   *
   * @return $this
   */
  public function setParentFormMapper(?FormMapper $parentFormMapper): self
  {
    $this->parentFormMapper = $parentFormMapper;
    return $this;
  }

  /**
   * @param array<string, FormMapper<T>> $allSubMappers
   * @return array<string, FormMapper<T>>
   */
  public function getAllSubFormMapper(array &$allSubMappers = []): array
  {
    $allSubMappers = [];
    foreach($this->getSubFormMappers() as $subFormMapper)
    {
      $allSubMappers[$subFormMapper->getName()] = $subFormMapper;
      $subFormMapper->getAllSubFormMapper($allSubMappers);
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
   * @return $this
   */
  public function setPathToTemplateDefault(?string $pathToTemplateDefault = null): self
  {
    $this->pathToTemplateDefault = $pathToTemplateDefault;
    if($this->subFormMappers)
    {
      /** @var FormMapper<T> $subFormMapper */
      foreach($this->subFormMappers as $subFormMapper)
      {
        $subFormMapper->setPathToTemplateDefault($this->pathToTemplateDefault);
      }
    }
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
   * @return $this
   */
  public function setRequestMethod(string $requestMethod): self
  {
    $this->requestMethod = $requestMethod;
    return $this;
  }

}