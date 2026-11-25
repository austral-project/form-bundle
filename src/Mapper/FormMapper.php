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

use Austral\FormBundle\Field\CollectionEmbedField;
use Austral\FormBundle\Field\Field;
use Austral\FormBundle\Layout\Fieldset;
use Austral\FormBundle\Layout\Group;
use Austral\FormBundle\Layout\LayoutNode;
use Austral\FormBundle\Layout\Modal;
use Random\RandomException;

/**
 * Austral Form Mapper.
 *
 * Definition of one form: its fields (a single index by name) and its layout. A FormMapper is not a service:
 * create one per form (FormMapperFactory).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class FormMapper
{
  /**
   * Fieldset that receives the fields added directly to the mapper.
   */
  public const string DEFAULT_FIELDSET = 'default';

  /**
   * @var array<string, Field>
   */
  private array $fields = [];

  /**
   * Root nodes by id.
   *
   * @var array<string, LayoutNode>
   */
  private array $nodes = [];

  /**
   * @var string
   */
  private string $translationDomain = 'form';

  /**
   * @var list<string>|null
   */
  private ?array $validationGroups = null;

  /**
   * @var bool
   */
  private bool $html5Validate = false;

  /**
   * @param string            $name      Name of the form (prefix of the HTML names)
   * @param class-string|null $dataClass Defaults to the class of $data
   * @param object|null       $data
   */
  public function __construct(
    private string $name = 'form',
    private ?string $dataClass = null,
    private ?object $data = null,
  ) {
    $this->dataClass ??= $data ? $data::class : null;
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

    return $this;
  }

  /**
   * @return class-string|null
   */
  public function getDataClass(): ?string
  {
    return $this->dataClass;
  }

  /**
   * @param class-string|null $dataClass
   *
   * @return $this
   */
  public function setDataClass(?string $dataClass): self
  {
    $this->dataClass = $dataClass;

    return $this;
  }

  /**
   * @return object|null
   */
  public function getObject(): ?object
  {
    return $this->data;
  }

  /**
   * @param object|null $data
   *
   * @return $this
   */
  public function setObject(?object $data): self
  {
    $this->data = $data;
    if ($data && null === $this->dataClass)
    {
      $this->dataClass = $data::class;
    }

    return $this;
  }

  /**
   * @return string
   */
  public function getTranslationDomain(): string
  {
    return $this->translationDomain;
  }

  /**
   * @param string $translationDomain
   *
   * @return $this
   */
  public function setTranslationDomain(string $translationDomain): self
  {
    $this->translationDomain = $translationDomain;

    return $this;
  }

  /**
   * @return list<string>|null
   */
  public function getValidationGroups(): ?array
  {
    return $this->validationGroups;
  }

  /**
   * @param list<string>|null $validationGroups
   *
   * @return $this
   */
  public function setValidationGroups(?array $validationGroups): self
  {
    $this->validationGroups = $validationGroups;

    return $this;
  }

  /**
   * @return bool
   */
  public function isHtml5Validate(): bool
  {
    return $this->html5Validate;
  }

  /**
   * @param bool $html5Validate
   *
   * @return $this
   */
  public function setHtml5Validate(bool $html5Validate): self
  {
    $this->html5Validate = $html5Validate;

    return $this;
  }

  /**
   * Adds a fieldset at the root, or returns the fieldset of the same name.
   *
   * @param string            $name
   * @param string|false|null $label    Translation key; the name when null, no title when false
   * @param string            $position Fieldset::POSITION_*
   *
   * @return Fieldset
   */
  public function addFieldset(string $name, string|false|null $label = null, string $position = Fieldset::POSITION_MASTER): Fieldset
  {
    $id = Fieldset::TYPE.'-'.LayoutNode::slug($name);
    $fieldset = $this->nodes[$id] ?? null;
    if ($fieldset instanceof Fieldset)
    {
      return $fieldset;
    }
    $fieldset = new Fieldset($name, $this, $label, $position);
    $this->nodes[$id] = $fieldset;

    return $fieldset;
  }

  /**
   * Adds a field to the default fieldset (position bottom, without title).
   *
   * @param Field|null $field
   * @param int|null   $order
   *
   * @return $this
   */
  public function add(?Field $field, ?int $order = null): self
  {
    $this->defaultFieldset()->add($field, $order);

    return $this;
  }

  /**
   * @param iterable<Field|null> $fields
   *
   * @return $this
   */
  public function adds(iterable $fields): self
  {
    $this->defaultFieldset()->adds($fields);

    return $this;
  }

  /**
   * Adds a group to the default fieldset.
   *
   * @param string            $name
   * @param string|false|null $label
   *
   * @return Group
   */
  public function addGroup(string $name, string|false|null $label = null): Group
  {
    return $this->defaultFieldset()->addGroup($name, $label);
  }

  /**
   * Adds a modal at the root, or returns the modal of the same name.
   *
   * @param string               $name
   * @param string|null          $fieldname
   * @param array<string, mixed> $options
   *
   * @return Modal
   */
  public function addModal(string $name, ?string $fieldname = null, array $options = []): Modal
  {
    $id = Modal::TYPE.'-'.LayoutNode::slug($name);
    $modal = $this->nodes[$id] ?? null;
    if ($modal instanceof Modal)
    {
      return $modal;
    }
    $modal = new Modal($name, $this, null, $fieldname, $options);
    $this->nodes[$id] = $modal;

    return $modal;
  }

  /**
   * @return $this
   */
  public function end(): self
  {
    return $this;
  }

  /**
   * @param string $name
   *
   * @return Fieldset|null
   */
  public function getFieldset(string $name): ?Fieldset
  {
    $fieldset = $this->nodes[Fieldset::TYPE.'-'.LayoutNode::slug($name)] ?? null;

    return $fieldset instanceof Fieldset ? $fieldset : null;
  }

  /**
   * @return list<Fieldset>
   */
  public function getFieldsets(): array
  {
    return array_values(array_filter($this->nodes, static fn (LayoutNode $node): bool => $node instanceof Fieldset));
  }

  /**
   * Finds a node by id anywhere in the layout ("fieldset-…", "group-…", "modal-…").
   *
   * @param string $id
   *
   * @return LayoutNode|null
   */
  public function findNode(string $id): ?LayoutNode
  {
    if (isset($this->nodes[$id]))
    {
      return $this->nodes[$id];
    }
    foreach ($this->nodes as $node)
    {
      if ($found = $node->findNode($id))
      {
        return $found;
      }
    }

    return null;
  }

  /**
   * Root nodes in display order: same rule as the children of a node (explicit order, otherwise insertion order).
   *
   * @return list<LayoutNode>
   */
  public function getNodes(): array
  {
    $nodes = [];
    foreach (array_values($this->nodes) as $index => $node)
    {
      $nodes[] = [$node, $node->getOrder() ?? $index, $index];
    }
    usort($nodes, static fn (array $a, array $b): int => [$a[1], $a[2]] <=> [$b[1], $b[2]]);

    return array_map(static fn (array $node): LayoutNode => $node[0], $nodes);
  }

  /**
   * Called by the layout nodes: a field with the same name is removed from the layout and replaced.
   *
   * @internal
   *
   * @param Field $field
   *
   * @return void
   */
  public function registerField(Field $field): void
  {
    if (isset($this->fields[$field->getName()]))
    {
      $this->remove($field->getName());
    }
    $field->setFormMapper($this);
    $this->fields[$field->getName()] = $field;
  }

  /**
   * @param string $name
   *
   * @return Field|null
   */
  public function getField(string $name): ?Field
  {
    return $this->fields[$name] ?? null;
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  public function hasField(string $name): bool
  {
    return isset($this->fields[$name]);
  }

  /**
   * @return array<string, Field>
   */
  public function getFields(): array
  {
    return $this->fields;
  }

  /**
   * Mappers of the entries of the collections of this form (and of their variants), not recursive.
   *
   * @return list<FormMapper>
   */
  public function getSubMappers(): array
  {
    $mappers = [];
    foreach ($this->fields as $field)
    {
      if ($field instanceof CollectionEmbedField)
      {
        array_push($mappers, ...$field->getMappers());
      }
    }

    return $mappers;
  }

  /**
   * Removes a field from the form and from the layout.
   *
   * @param string $name
   *
   * @return $this
   */
  public function remove(string $name): self
  {
    if (isset($this->fields[$name]))
    {
      $this->fields[$name]->setFormMapper(null);
      unset($this->fields[$name]);
    }
    foreach ($this->nodes as $node)
    {
      $node->removeField($name);
    }

    return $this;
  }

  /**
   * @return Fieldset
   * @throws RandomException
   */
  private function defaultFieldset(): Fieldset
  {
    return $this->addFieldset(self::DEFAULT_FIELDSET, false, Fieldset::POSITION_BOTTOM)->setShowLabel(false);
  }
}
