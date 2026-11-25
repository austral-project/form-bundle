<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Field;

use Austral\Contracts\Entity\EntityInterface;
use Austral\FormBundle\Field\Base\Field;
use Austral\FormBundle\Field\Base\FieldInterface;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Mapper\Modal;
use Austral\ToolsBundle\AustralTools;
use Closure;
use Random\RandomException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field CollectionEmbed Input.
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class CollectionEmbedField extends Field
{

  /**
   * @var array<string, CollectionEmbedField<T>>
   */
  protected array $collectionsForms = [];

  /**
   * @var CollectionEmbedField<T>|null
   */
  protected ?CollectionEmbedField $collectionParent = null;

  /**
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, array $options = []): self
  {
    return new self($fieldname, $options);
  }

  /**
   * CollectionEmbedField constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, array $options = [])
  {
    if(!array_key_exists("entitled", $options))
    {
      $options["entitled"] = false;
    }
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = CollectionType::class;
    $this->widgetInput = false;
    if($this->isDefaultTemplate)
    {
      $this->options["template"]["path"] = "collection-embed.html.twig";
    }
  }

  /**
   * @return string|null
   */
  public function getEntitledButton(): ?string
  {
    return $this->options['button'];
  }

  /**
   * @inheritDoc
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults(array(
      "title"                 =>  null,
      "button"                =>  null,
      "sortable"              =>  function(OptionsResolver $subResolver ) {
        $subResolver->setDefaults(array(
            "value"       =>  null,
            "editable"    =>  false,
            "key_after"   =>  false
          )
        );
        $subResolver->setAllowedTypes('value', array('string', Closure::class, "null", "array"))
          ->setAllowedTypes('editable', array("bool"))
          ->setAllowedTypes('key_after', array("bool"));
      },
      "master_children"       =>  false,
      "view_position"         =>  false,
      "between_insert"        =>  false,

      "collections"           =>  function(OptionsResolver $subResolver ) {
        $subResolver->setDefaults(array(
            "choices"  =>  [],
            "objects"  =>  [],
            "children" =>  [],
          )
        );
      },

      "allow"                 =>  function(OptionsResolver $subResolver ) {
        $subResolver->setDefaults(array(
            "child"                 =>  false,
            "add"                   =>  true,
            "delete"                =>  true,
          )
        );
      },

      "children_parameters"    =>  function(OptionsResolver $subResolver ) {
        $subResolver->setDefaults(array(
            "min"                   =>  null,
            "max"                   =>  null,
          )
        );
      },

      "color"               =>  0,

      "getter"              =>  null,
      "setter"              =>  null,

      "entry"               =>  function(OptionsResolver $subResolver ) {
        $subResolver->setDefaults(array(
            "label"               =>  false,
            "type"                =>  null,
            "attr"                =>  []
          )
        );
      },

      'error_bubbling'      =>  true,
      "prototype"           =>  function(OptionsResolver $subResolver ) {
          $subResolver->setDefaults(array(
            "data"                =>  [],
            "name"                =>  "__name__"
          )
        );
      },
    ));
  }

  /**
   * allowChild
   *
   * @return bool
   */
  public function allowChild(): bool
  {
    return $this->options["allow"]["child"];
  }

  /**
   * masterChildren
   *
   * @return bool
   */
  public function masterChildren(): bool
  {
    return $this->options["master_children"];
  }

  /**
   * viewPosition
   *
   * @return bool
   */
  public function viewPosition(): bool
  {
    return $this->options["view_position"];
  }

  /**
   * childrenParameters
   *
   * @return array{min: int|null, max: int|null}
   */
  public function childrenParameters(): array
  {
    return $this->options["children_parameters"];
  }

  /**
   * @return bool
   */
  public function betweenInsert(): bool
  {
    return $this->options["between_insert"];
  }

  /**
   * @inheritDoc
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions['prototype_name'] = array_key_exists("prototype_name", $fieldOptions) ? $fieldOptions["prototype_name"] : $this->options["prototype"]["name"];
    $fieldOptions['prototype_data'] = array_key_exists("prototype_data", $fieldOptions) ? $fieldOptions["prototype_data"] : $this->options["prototype"]["data"];
    $fieldOptions['allow_add'] = array_key_exists("allow_add", $fieldOptions) ? $fieldOptions["allow_add"] : $this->options["allow"]["add"];
    $fieldOptions['allow_delete'] = array_key_exists("allow_delete", $fieldOptions) ? $fieldOptions["allow_delete"] : $this->options["allow"]["delete"];
    $fieldOptions['error_bubbling'] = array_key_exists("error_bubbling", $fieldOptions) ? $fieldOptions["error_bubbling"] : $this->options["error_bubbling"];
    $fieldOptions['entry_options'] = array_key_exists("entry_options", $fieldOptions) ? $fieldOptions["entry_options"] : [];
    $fieldOptions['entry_options']['label'] = array_key_exists("label", $fieldOptions['entry_options']) ? $fieldOptions["entry_options"]["label"] : $this->options["entry"]["label"];
    $fieldOptions['entry_options']['attr'] = array_key_exists("attr", $fieldOptions['entry_options']) ? $fieldOptions["entry_options"]["attr"] : $this->options["entry"]["attr"];
    $fieldOptions['entry_type'] = array_key_exists("entry_type", $fieldOptions) ? $fieldOptions["entry_type"] : $this->options["entry"]["type"];
    return $fieldOptions;
  }

  /**
   * @param string $key
   * @param CollectionEmbedField<T> $collectionsForm
   *
   * @return $this
   */
  public function addCollectionForm(string $key, CollectionEmbedField $collectionsForm): self
  {
    $this->symfonyFormType = null;
    if($collectionsForm->getFormMapper())
    {
      $this->renameModalKeyId($collectionsForm->getFormMapper(), $collectionsForm);
    }
    $collectionsForm->setCollectionParent($this);
    $this->collectionsForms[$key] = $collectionsForm;
    return $this;
  }

  /**
   * @param array<string, CollectionEmbedField<T>> $collectionsForms
   *
   * @return $this
   */
  public function addCollectionsForms(array $collectionsForms): self
  {
    $this->symfonyFormType = null;
    $this->collectionsForms = [];
    /**
     * @var string $key
     * @var CollectionEmbedField<T> $collectionsForm
     */
    foreach ($collectionsForms as $key => $collectionsForm)
    {
      $this->addCollectionForm($key, $collectionsForm);
    }
    $this->collectionsForms = $collectionsForms;
    return $this;
  }

  /**
   * initCollectionsForm
   *
   * @return $this
   */
  public function initCollectionForm(): self
  {
    if(!$this->collectionsForms && $this->getFormMapper())
    {
      $this->renameModalKeyId($this->getFormMapper(), $this);
    }
    return $this;
  }

  /**
   * @param FormMapper<T> $formMapper
   * @param FieldInterface<T> $collectionsForm
   */
  protected function renameModalKeyId(FormMapper $formMapper, FieldInterface $collectionsForm): void
  {
    if($formMapper->getModals())
    {
      /** @var Modal<T> $modal */
      foreach($formMapper->getModals() as $modal)
      {
        $modal->setModalKeyId($modal->getModalKeyId()."_".$collectionsForm->getOptions()['prototype']['name']);
      }
    }
    elseif($subMappers = $formMapper->getSubFormMappers())
    {
      /** @var FormMapper<T> $subMapper */
      foreach ($subMappers as $subMapper)
      {
        $this->renameModalKeyId($subMapper, $collectionsForm);
      }
    }
  }


  /**
   * @return array<string, FieldInterface<T>>
   */
  public function getCollectionsForms(): array
  {
    return $this->collectionsForms;
  }

  /**
   * @return array<string, array<string, mixed>>
   */
  public function getCollectionsChoices(): array
  {
    return $this->options['collections']['choices'];
  }

  /**
   * @return array<string, array<string, mixed>>
   */
  public function getSelectCollectionChoices(): array
  {
    $collectionsChoicesFinally = [];
    foreach($this->options['collections']['choices'] as $key => $values)
    {
      $category = AustralTools::valueByKey($values, "category", "default");
      $category = $category == "default" ? "" : $category;
      $collectionsChoicesFinally[$category][$key] = $values;
    }
    ksort($collectionsChoicesFinally);
    return $collectionsChoicesFinally;
  }

  /**
   * @param EntityInterface<T>|null $object
   * @param mixed|null $default
   * @return mixed
   */
  public function getCollectionsObjects(?EntityInterface $object = null, mixed $default = []): mixed
  {
    return $this->executeClosureFunction($this->options['collections']['objects'], $object, $default);
  }

  /**
   * @param EntityInterface<T>|null $object
   * @param mixed|null $default
   * @return mixed
   */
  public function getCollectionsChildren(?EntityInterface $object = null, mixed $default = null): mixed
  {
    return $this->executeClosureFunction($this->options['collections']['children'], $object, $default);
  }

  /**
   * @param EntityInterface<T>|null $object
   * @param mixed|null $default
   * @return string
   * @throws RandomException
   */
  public function getSortable(?EntityInterface $object = null, mixed $default = null): string
  {
    $value = $this->executeClosureFunction($this->options['sortable']['value'], $object, $default);
    if(is_int($value) || is_float($value))
    {
      $value = $value < 10 ? "0{$value}" : $value;
    }
    return $value . ($this->options['sortable']['key_after'] ? AustralTools::random() : null);
  }

  /**
   * @return bool
   */
  public function hasSortable(): bool
  {
    return $this->options['sortable']['editable'];
  }

  /**
   * @return CollectionEmbedField<T>|null
   */
  public function getCollectionParent(): ?CollectionEmbedField
  {
    return $this->collectionParent;
  }

  /**
   * @param CollectionEmbedField<T>|null $collectionParent
   *
   * @return $this
   */
  public function setCollectionParent(?CollectionEmbedField $collectionParent = null): self
  {
    $this->collectionParent = $collectionParent;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getTitle(): ?string
  {
    return $this->options['title'];
  }

}