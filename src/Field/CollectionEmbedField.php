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

use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Austral Collection Embed Field.
 *
 * CollectionType whose entries are Austral forms: entry.mapper is the FormMapper of an entry (it replaces the
 * 3.x entry.type + formMapperKey). Each entry gets its own form.vars.austral_layout (ids from its view id, so
 * unique per entry and in the prototype) and form.vars.austral_entry (its position, from "sortable").
 *
 * With variants (addVariant(), content-block: one collection by type of component), this field is a container:
 * it is not added to the form, its variants are (each one is a collection of its own, usually with a getter
 * and a setter filtering the same property), and the layout shows them in a "variants" node.
 *
 * Not final: other bundles build their collections on it (ContentBlockField of content-block-bundle).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class CollectionEmbedField extends Field
{
  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = CollectionType::class;

  /**
   * @var array<string, CollectionEmbedField>
   */
  private array $variants = [];

  /**
   * @param string               $name
   * @param array<string, mixed> $options
   *
   * @return self
   */
  public static function create(string $name, array $options = []): self
  {
    return new self($name, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults([
      'label' => false,
      'title' => null,
      'button' => null,
      'color' => 0,
      'master_children' => false,
      'view_position' => false,
      'between_insert' => false,
      'error_bubbling' => true,
    ]);

    $resolver->setAllowedTypes('title', ['null', 'string'])
      ->setAllowedTypes('button', ['null', 'string'])
      ->setAllowedTypes('color', 'int')
      ->setAllowedTypes('master_children', 'bool')
      ->setAllowedTypes('view_position', 'bool')
      ->setAllowedTypes('between_insert', 'bool')
      ->setAllowedTypes('error_bubbling', 'bool');

    $resolver->setOptions('entry', static function (OptionsResolver $entry): void {
      $entry->setDefaults(['mapper' => null, 'type' => null, 'label' => false, 'attr' => []])
        ->setAllowedTypes('mapper', ['null', FormMapper::class])
        ->setAllowedTypes('type', ['null', 'string'])
        ->setAllowedTypes('label', ['null', 'bool', 'string'])
        ->setAllowedTypes('attr', 'array');
    });

    $resolver->setOptions('allow', static function (OptionsResolver $allow): void {
      $allow->setDefaults(['child' => false, 'add' => true, 'delete' => true])
        ->setAllowedTypes('child', 'bool')
        ->setAllowedTypes('add', 'bool')
        ->setAllowedTypes('delete', 'bool');
    });

    $resolver->setOptions('children_parameters', static function (OptionsResolver $children): void {
      $children->setDefaults(['min' => null, 'max' => null])
        ->setAllowedTypes('min', ['null', 'int'])
        ->setAllowedTypes('max', ['null', 'int']);
    });

    $resolver->setOptions('prototype', static function (OptionsResolver $prototype): void {
      $prototype->setDefaults(['data' => null, 'name' => '__name__'])
        ->setAllowedTypes('name', 'string');
    });

    $resolver->setOptions('sortable', static function (OptionsResolver $sortable): void {
      $sortable->setDefaults(['value' => null, 'editable' => false, 'key_after' => false])
        ->setAllowedTypes('value', ['null', 'string', \Closure::class])
        ->setAllowedTypes('editable', 'bool')
        ->setAllowedTypes('key_after', 'bool');
    });

    $resolver->setOptions('collections', static function (OptionsResolver $collections): void {
      $collections->setDefaults(['choices' => [], 'objects' => null, 'children' => null])
        ->setAllowedTypes('choices', 'array')
        ->setAllowedTypes('objects', ['null', 'string', \Closure::class])
        ->setAllowedTypes('children', ['null', 'string', \Closure::class]);
    });
  }

  /**
   * @return FormMapper|null
   */
  public function getEntryMapper(): ?FormMapper
  {
    return $this->options['entry']['mapper'];
  }

  /**
   * Mappers of the entries of this collection and of its variants (to dispatch the form events to them).
   *
   * @return list<FormMapper>
   */
  public function getMappers(): array
  {
    $mappers = $this->getEntryMapper() ? [$this->getEntryMapper()] : [];
    foreach ($this->variants as $variant)
    {
      array_push($mappers, ...$variant->getMappers());
    }

    return $mappers;
  }

  /**
   * Adds a variant (replaces the 3.x addCollectionForm()). $key is the key of the variant in collections.choices.
   *
   * @param string               $key
   * @param CollectionEmbedField $variant
   *
   * @return $this
   */
  public function addVariant(string $key, CollectionEmbedField $variant): static
  {
    $this->variants[$key] = $variant;

    return $this;
  }

  /**
   * @param array<string, CollectionEmbedField> $variants
   *
   * @return $this
   */
  public function setVariants(array $variants): static
  {
    $this->variants = [];
    foreach ($variants as $key => $variant)
    {
      $this->addVariant($key, $variant);
    }

    return $this;
  }

  /**
   * @return array<string, CollectionEmbedField>
   */
  public function getVariants(): array
  {
    return $this->variants;
  }

  /**
   * @return bool
   */
  public function hasVariants(): bool
  {
    return [] !== $this->variants;
  }

  /**
   * Choices of the variants grouped by "category" (the default category is ""), sorted by category.
   *
   * @return array<string, array<string, mixed>>
   */
  public function getGroupedChoices(): array
  {
    $grouped = [];
    foreach ($this->options['collections']['choices'] as $key => $choice)
    {
      $category = \is_array($choice) ? ($choice['category'] ?? 'default') : 'default';
      $grouped['default' === $category ? '' : $category][$key] = $choice;
    }
    ksort($grouped);

    return $grouped;
  }

  /**
   * Position of an entry: "sortable.value" (property or closure receiving the entry), on two digits under 10,
   * followed by a random suffix with "sortable.key_after" (as in 3.x).
   *
   * @param object|null $entry
   *
   * @return string
   */
  public function getSortable(?object $entry): string
  {
    $value = self::read($this->options['sortable']['value'], $entry);
    if (\is_int($value) || \is_float($value))
    {
      $value = $value < 10 ? "0{$value}" : (string) $value;
    }
    $value = \is_scalar($value) ? (string) $value : '';

    return $value.($this->options['sortable']['key_after'] ? bin2hex(random_bytes(4)) : '');
  }

  /**
   * @param object|null $data
   *
   * @return mixed
   */
  public function getCollectionsObjects(?object $data): mixed
  {
    return self::read($this->options['collections']['objects'], $data);
  }

  /**
   * @param object|null $data
   *
   * @return mixed
   */
  public function getCollectionsChildren(?object $data): mixed
  {
    return self::read($this->options['collections']['children'], $data);
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $entry = $this->options['entry'];
    $options += [
      'allow_add' => $this->options['allow']['add'],
      'allow_delete' => $this->options['allow']['delete'],
      'prototype_name' => $this->options['prototype']['name'],
      'error_bubbling' => $this->options['error_bubbling'],
    ];
    if (null !== $this->options['prototype']['data'])
    {
      $options += ['prototype_data' => $this->options['prototype']['data']];
    }
    $entryType = $entry['mapper'] ? AustralFormType::class : $entry['type'];
    if (null !== $entryType)
    {
      $options += ['entry_type' => $entryType];
    }
    $entryOptions = ($options['entry_options'] ?? []) + ['label' => $entry['label'], 'attr' => $entry['attr']];
    if ($entry['mapper'])
    {
      $entryOptions += [
        'mapper' => $entry['mapper'],
        'entry_vars' => fn (?object $data): array => ['sortable' => $this->getSortable($data)],
      ];
    }
    $options['entry_options'] = $entryOptions;

    return $options;
  }

  /**
   * A container of variants adds its variants instead of itself.
   *
   * {@inheritdoc}
   */
  public function addToForm(FormBuilderInterface $builder, array $options): void
  {
    if (!$this->hasVariants())
    {
      parent::addToForm($builder, $options);

      return;
    }
    foreach ($this->variants as $variant)
    {
      $variant->addToForm($builder, $variant->getSymfonyOptions() + ['austral' => $variant->getPresentation()]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getWidget(): array
  {
    return [
      'title' => $this->options['title'],
      'button' => $this->options['button'],
      'color' => $this->options['color'],
      'allow' => $this->options['allow'],
      'children_parameters' => $this->options['children_parameters'],
      'master_children' => $this->options['master_children'],
      'view_position' => $this->options['view_position'],
      'between_insert' => $this->options['between_insert'],
      'sortable' => $this->options['sortable']['editable'],
      'prototype_name' => $this->options['prototype']['name'],
      'choices' => $this->getGroupedChoices(),
    ];
  }

  /**
   * A property of $data (read with the property accessor), or the result of a closure receiving $data.
   *
   * @param string|\Closure|null $reader
   * @param object|null          $data
   *
   * @return mixed
   */
  private static function read(string|\Closure|null $reader, ?object $data): mixed
  {
    if ($reader instanceof \Closure)
    {
      return $reader($data);
    }
    if (null === $reader || null === $data)
    {
      return null;
    }
    $accessor = PropertyAccess::createPropertyAccessor();

    return $accessor->isReadable($data, $reader) ? $accessor->getValue($data, $reader) : null;
  }
}
