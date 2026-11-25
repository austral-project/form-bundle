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

use Austral\FormBundle\Field\Field;
use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Austral Layout Node.
 *
 * A node of the layout (fieldset, group, modal). It only stores field names: the fields themselves live in the FormMapper.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
abstract class LayoutNode
{
  /**
   * Prefix of the node id ("fieldset", "group", "modal").
   */
  public const string TYPE = '';

  /**
   * @var string
   */
  protected string $id;

  /**
   * @var array<string, mixed>
   */
  protected array $attr = [];

  /**
   * @var int|null
   */
  protected ?int $order = null;

  /**
   * @var bool|\Closure
   */
  protected bool|\Closure $visible = true;

  /**
   * Children by key ("field:<name>" or "node:<id>"): [field name or node, order, insertion index].
   *
   * @var array<string, array{0: string|LayoutNode, 1: ?int, 2: int}>
   */
  private array $children = [];

  /**
   * @var int
   */
  private int $sequence = 0;

  /**
   * @param string            $name
   * @param FormMapper        $mapper
   * @param LayoutNode|null   $parent
   * @param string|false|null $label
   */
  public function __construct(
    protected string $name,
    protected FormMapper $mapper,
    protected ?LayoutNode $parent = null,
    protected string|false|null $label = null,
  ) {
    $this->id = static::TYPE.'-'.self::slug($name);
  }

  /**
   * @param string $name
   *
   * @return string
   */
  public static function slug(string $name): string
  {
    return strtolower(new AsciiSlugger()->slug($name)->toString());
  }

  /**
   * @return string
   */
  public function getId(): string
  {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @return string|false|null
   */
  public function getLabel(): string|false|null
  {
    return $this->label;
  }

  /**
   * @param string|false|null $label
   *
   * @return $this
   */
  public function setLabel(string|false|null $label): static
  {
    $this->label = $label;

    return $this;
  }

  /**
   * @return array<string, mixed>
   */
  public function getAttr(): array
  {
    return $this->attr;
  }

  /**
   * @param array<string, mixed> $attr
   *
   * @return $this
   */
  public function setAttr(array $attr): static
  {
    $this->attr = $attr;

    return $this;
  }

  /**
   * @return int|null
   */
  public function getOrder(): ?int
  {
    return $this->order;
  }

  /**
   * Position of the node among its siblings (null: insertion order).
   *
   * @param int|null $order
   *
   * @return $this
   */
  public function setOrder(?int $order): static
  {
    $this->order = $order;
    $this->parent?->reorder($this);

    return $this;
  }

  /**
   * @return bool
   */
  public function isVisible(): bool
  {
    return \is_bool($this->visible) ? $this->visible : (bool) ($this->visible)($this);
  }

  /**
   * @param bool|\Closure(LayoutNode): bool $visible
   *
   * @return $this
   */
  public function setVisible(bool|\Closure $visible): static
  {
    $this->visible = $visible;

    return $this;
  }

  /**
   * @return LayoutNode|null
   */
  public function getParent(): ?LayoutNode
  {
    return $this->parent;
  }

  /**
   * @return FormMapper
   */
  public function getFormMapper(): FormMapper
  {
    return $this->mapper;
  }

  /**
   * Returns the parent node, or the FormMapper for a node at the root.
   *
   * @return LayoutNode|FormMapper
   */
  public function end(): LayoutNode|FormMapper
  {
    return $this->parent ?? $this->mapper;
  }

  /**
   * Adds a field to this node. A field with the same name elsewhere in the form is replaced.
   *
   * @param Field|null $field
   * @param int|null   $order
   *
   * @return $this
   */
  public function add(?Field $field, ?int $order = null): static
  {
    if (null === $field)
    {
      return $this;
    }
    $this->mapper->registerField($field);
    $this->children['field:'.$field->getName()] = [$field->getName(), $order, $this->sequence++];

    return $this;
  }

  /**
   * @param iterable<Field|null> $fields
   *
   * @return $this
   */
  public function adds(iterable $fields): static
  {
    foreach ($fields as $field)
    {
      $this->add($field);
    }

    return $this;
  }

  /**
   * Adds a group, or returns the group of the same name in this node.
   *
   * @param string            $name
   * @param string|false|null $label
   *
   * @return Group
   */
  public function addGroup(string $name, string|false|null $label = null): Group
  {
    $group = $this->childNode(Group::TYPE.'-'.self::slug($name));
    if ($group instanceof Group)
    {
      return $group;
    }

    return $this->appendNode(new Group($name, $this->mapper, $this, $label));
  }

  /**
   * Adds a modal, or returns the modal of the same name in this node.
   *
   * @param string               $name
   * @param string|null          $fieldname Field whose button opens the modal
   * @param array<string, mixed> $options
   *
   * @return Modal
   */
  public function addModal(string $name, ?string $fieldname = null, array $options = []): Modal
  {
    $modal = $this->childNode(Modal::TYPE.'-'.self::slug($name));
    if ($modal instanceof Modal)
    {
      return $modal;
    }

    return $this->appendNode(new Modal($name, $this->mapper, $this, $fieldname, $options));
  }

  /**
   * @template N of LayoutNode
   *
   * @param N $node
   *
   * @return N
   */
  protected function appendNode(LayoutNode $node): LayoutNode
  {
    $this->children['node:'.$node->getId()] = [$node, $node->getOrder(), $this->sequence++];

    return $node;
  }

  /**
   * @param string $id
   *
   * @return LayoutNode|null
   */
  public function childNode(string $id): ?LayoutNode
  {
    $child = $this->children['node:'.$id] ?? null;

    return $child && $child[0] instanceof LayoutNode ? $child[0] : null;
  }

  /**
   * Finds a node by id in this node and its descendants.
   *
   * @param string $id
   *
   * @return LayoutNode|null
   */
  public function findNode(string $id): ?LayoutNode
  {
    if ($node = $this->childNode($id))
    {
      return $node;
    }
    foreach ($this->getChildNodes() as $child)
    {
      if ($node = $child->findNode($id))
      {
        return $node;
      }
    }

    return null;
  }

  /**
   * Removes a field name from this node and its descendants.
   *
   * @param string $fieldname
   *
   * @return bool true if the field was found
   */
  public function removeField(string $fieldname): bool
  {
    $removed = false;
    if (\array_key_exists('field:'.$fieldname, $this->children))
    {
      unset($this->children['field:'.$fieldname]);
      $removed = true;
    }
    foreach ($this->getChildNodes() as $child)
    {
      $removed = $child->removeField($fieldname) || $removed;
    }

    return $removed;
  }

  /**
   * @param string $fieldname
   *
   * @return bool
   */
  public function hasField(string $fieldname): bool
  {
    return \array_key_exists('field:'.$fieldname, $this->children);
  }

  /**
   * Children sorted by order: an explicit order competes with the insertion index of the other children.
   *
   * @return list<string|LayoutNode> field names and nodes
   */
  public function getChildren(): array
  {
    $children = array_values($this->children);
    usort($children, static fn (array $a, array $b): int => [$a[1] ?? $a[2], $a[2]] <=> [$b[1] ?? $b[2], $b[2]]);

    return array_map(static fn (array $child): string|LayoutNode => $child[0], $children);
  }

  /**
   * @return list<string>
   */
  public function getFieldNames(): array
  {
    return array_values(array_filter($this->getChildren(), 'is_string'));
  }

  /**
   * @return list<LayoutNode>
   */
  public function getChildNodes(): array
  {
    return array_values(array_filter($this->getChildren(), static fn (string|LayoutNode $child): bool => $child instanceof LayoutNode));
  }

  /**
   * @param LayoutNode $node
   *
   * @return void
   */
  private function reorder(LayoutNode $node): void
  {
    $key = 'node:'.$node->getId();
    if (isset($this->children[$key]))
    {
      $this->children[$key][1] = $node->getOrder();
    }
  }
}
