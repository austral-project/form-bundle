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

use Austral\FormBundle\Field\Base\Field;
use Austral\FormBundle\Field\Base\FieldInterface;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Mapper\Popin;
use Austral\ToolsBundle\AustralTools;
use Closure;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field CollectionEmbed Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class CollectionEmbedField extends Field
{

  /**
   * @var array
   */
  protected array $collectionsForms = array();

  /**
   * @var CollectionEmbedField|null
   */
  protected ?CollectionEmbedField $collectionParent = null;

  /**
   * @param string $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create(string $fieldname, array $options = array()): CollectionEmbedField
  {
    return new self($fieldname, $options);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
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
   * @param OptionsResolver $resolver
   */
  protected function configureOptions(OptionsResolver $resolver)
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
            "choices"  =>  array(),
            "objects"  =>  array(),
            "children" =>  array(),
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

      "color"               =>  0,

      "getter"              =>  null,
      "setter"              =>  null,

      "entry"               =>  function(OptionsResolver $subResolver ) {
        $subResolver->setDefaults(array(
            "label"               =>  false,
            "type"                =>  null,
            "attr"                =>  array()
          )
        );
      },

      'error_bubbling'      =>  true,
      "prototype"           =>  function(OptionsResolver $subResolver ) {
          $subResolver->setDefaults(array(
            "data"                =>  array(),
            "name"                =>  "__name__"
          )
        );
      },
    ));
  }

  public function allowChild()
  {
    return $this->options["allow"]["child"];
  }

  public function masterChildren()
  {
    return $this->options["master_children"];
  }

  public function viewPosition()
  {
    return $this->options["view_position"];
  }

  /**
   * @return mixed
   */
  public function widgetNotRendering()
  {
    return $this->options["widgetNotRendering"];
  }

  /**
   * @return mixed
   */
  public function betweenInsert()
  {
    return $this->options["between_insert"];
  }

  /**
   * Get options
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions['prototype_name'] = array_key_exists("prototype_name", $fieldOptions) ? $fieldOptions["prototype_name"] : $this->options["prototype"]["name"];
    $fieldOptions['prototype_data'] = array_key_exists("prototype_data", $fieldOptions) ? $fieldOptions["prototype_data"] : $this->options["prototype"]["data"];
    $fieldOptions['allow_add'] = array_key_exists("allow_add", $fieldOptions) ? $fieldOptions["allow_add"] : $this->options["allow"]["add"];
    $fieldOptions['allow_delete'] = array_key_exists("allow_delete", $fieldOptions) ? $fieldOptions["allow_delete"] : $this->options["allow"]["delete"];
    $fieldOptions['error_bubbling'] = array_key_exists("error_bubbling", $fieldOptions) ? $fieldOptions["error_bubbling"] : $this->options["error_bubbling"];
    $fieldOptions['entry_options'] = array_key_exists("entry_options", $fieldOptions) ? $fieldOptions["entry_options"] : array();
    $fieldOptions['entry_options']['label'] = array_key_exists("label", $fieldOptions['entry_options']) ? $fieldOptions["entry_options"]["label"] : $this->options["entry"]["label"];
    $fieldOptions['entry_options']['attr'] = array_key_exists("attr", $fieldOptions['entry_options']) ? $fieldOptions["entry_options"]["attr"] : $this->options["entry"]["attr"];
    $fieldOptions['entry_type'] = array_key_exists("entry_type", $fieldOptions) ? $fieldOptions["entry_type"] : $this->options["entry"]["type"];
    return $fieldOptions;
  }

  /**
   * @param string $key
   * @param FieldInterface $collectionsForms
   *
   * @return CollectionEmbedField
   */
  public function addCollectionForm(string $key, FieldInterface $collectionsForms): CollectionEmbedField
  {
    $this->symfonyFormType = null;
    $collectionsForms->setCollectionParent($this);
    $this->collectionsForms[$key] = $collectionsForms;
    return $this;
  }

  /**
   * @param array $collectionsForms
   *
   * @return CollectionEmbedField
   */
  public function addCollectionsForms(array $collectionsForms): CollectionEmbedField
  {
    $this->symfonyFormType = null;
    /**
     * @var string $key
     * @var  CollectionEmbedField $collectionsForm
     */
    foreach ($collectionsForms as $key => $collectionsForm)
    {
      if($collectionsForm->getFormMapper())
      {
        $this->renamePopinKeyId($collectionsForm->getFormMapper(), $collectionsForm);
      }
      $collectionsForm->setCollectionParent($this);
      $collectionsForms[$key] = $collectionsForm;
    }
    $this->collectionsForms = $collectionsForms;
    return $this;
  }

  /**
   * @param FormMapper $formMapper
   * @param $collectionsForm
   */
  protected function renamePopinKeyId(FormMapper $formMapper, $collectionsForm)
  {
    if($formMapper->getPopins())
    {
      /** @var Popin $popin */
      foreach($formMapper->getPopins() as $popin)
      {
        $popin->setPopinKeyId($popin->getPopinKeyId()."_".$collectionsForm->getOptions()['prototype']['name']);
      }
    }
    elseif($subMappers = $formMapper->getSubFormMappers())
    {
      /** @var FormMapper $subMapper */
      foreach ($subMappers as $subMapper)
      {
        $this->renamePopinKeyId($subMapper, $collectionsForm);
      }
    }
  }


  /**
   * @return array
   */
  public function getCollectionsForms(): array
  {
    return $this->collectionsForms;
  }

  /**
   * @return array
   */
  public function getCollectionsChoices(): array
  {
    return $this->options['collections']['choices'];
  }

  /**
   * @return array
   */
  public function getSelectCollectionChoices(): array
  {
    $collectionsChoicesFinally = array();
    foreach($this->options['collections']['choices'] as $key => $values)
    {
      $category = AustralTools::getValueByKey($values, "category", "default");
      $category = $category == "default" ? "" : $category;
      $collectionsChoicesFinally[$category][$key] = $values;
    }
    ksort($collectionsChoicesFinally);
    return $collectionsChoicesFinally;
  }

  /**
   * @return string|null|Closure
   */
  public function getCollectionsObjects($object = null, $default = array())
  {
    return $this->executeClosureFunction($this->options['collections']['objects'], $object, $default);
  }

  /**
   * @return string|null|Closure
   */
  public function getCollectionsChildren($object = null, $default = array())
  {
    return $this->executeClosureFunction($this->options['collections']['children'], $object, $default);
  }

  /**
   * @return string
   */
  public function getSortable($object = null, $default = null): string
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
   * @return CollectionEmbedField|null
   */
  public function getCollectionParent(): ?CollectionEmbedField
  {
    return $this->collectionParent;
  }

  /**
   * @param CollectionEmbedField|null $collectionParent
   *
   * @return CollectionEmbedField
   */
  public function setCollectionParent(?CollectionEmbedField $collectionParent): CollectionEmbedField
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