<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Field;

use Austral\FormBundle\Field\CollectionEmbedField;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\GalleryItem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

final class CollectionEmbedFieldTest extends TestCase
{
  public function testDefaults(): void
  {
    $field = CollectionEmbedField::create('items');
    $options = $field->getSymfonyOptions();

    self::assertSame(CollectionType::class, $field->getSymfonyType());
    self::assertFalse($field->getLabel());
    self::assertTrue($options['allow_add']);
    self::assertTrue($options['allow_delete']);
    self::assertSame('__name__', $options['prototype_name']);
    self::assertTrue($options['error_bubbling']);
    self::assertArrayNotHasKey('prototype_data', $options);
    self::assertArrayNotHasKey('entry_type', $options);
    self::assertSame(['label' => false, 'attr' => []], $options['entry_options']);
    self::assertNull($field->getEntryMapper());
  }

  public function testEntriesFromAMapper(): void
  {
    $mapper = new FormMapper('item', GalleryItem::class);
    $prototype = new GalleryItem();
    $options = CollectionEmbedField::create('items', [
      'entry' => ['mapper' => $mapper, 'attr' => ['class' => 'item']],
      'prototype' => ['data' => $prototype, 'name' => '__item__'],
      'allow' => ['delete' => false],
    ])->getSymfonyOptions();

    self::assertSame(AustralFormType::class, $options['entry_type']);
    self::assertSame($mapper, $options['entry_options']['mapper']);
    self::assertSame(['class' => 'item'], $options['entry_options']['attr']);
    self::assertInstanceOf(\Closure::class, $options['entry_options']['entry_vars']);
    self::assertSame($prototype, $options['prototype_data']);
    self::assertSame('__item__', $options['prototype_name']);
    self::assertFalse($options['allow_delete']);
  }

  public function testEntriesOfASymfonyType(): void
  {
    $options = CollectionEmbedField::create('keywords', ['entry' => ['type' => TextType::class]])->getSymfonyOptions();

    self::assertSame(TextType::class, $options['entry_type']);
    self::assertArrayNotHasKey('mapper', $options['entry_options']);
  }

  public function testFormOptionsWin(): void
  {
    $options = CollectionEmbedField::create('items', ['form_options' => ['allow_add' => false, 'entry_options' => ['label' => 'Item']]])->getSymfonyOptions();

    self::assertFalse($options['allow_add']);
    self::assertSame('Item', $options['entry_options']['label']);
  }

  public function testSortable(): void
  {
    $item = new GalleryItem('A', 3);

    self::assertSame('03', CollectionEmbedField::create('items', ['sortable' => ['value' => 'position']])->getSortable($item));
    self::assertSame('12', CollectionEmbedField::create('items', ['sortable' => ['value' => static fn (?GalleryItem $item): int => 12]])->getSortable($item));
    self::assertSame('A', CollectionEmbedField::create('items', ['sortable' => ['value' => 'label']])->getSortable($item));
    self::assertSame('', CollectionEmbedField::create('items', ['sortable' => ['value' => 'unknown']])->getSortable($item));
    self::assertSame('', CollectionEmbedField::create('items')->getSortable(null));
    self::assertMatchesRegularExpression('/^03[0-9a-f]{8}$/', CollectionEmbedField::create('items', ['sortable' => ['value' => 'position', 'key_after' => true]])->getSortable($item));
  }

  public function testCollectionsObjectsAndChildren(): void
  {
    $item = new GalleryItem('A', 3);
    $field = CollectionEmbedField::create('items', ['collections' => ['objects' => 'label', 'children' => static fn (?GalleryItem $item): array => ['child']]]);

    self::assertSame('A', $field->getCollectionsObjects($item));
    self::assertSame(['child'], $field->getCollectionsChildren($item));
  }

  public function testVariants(): void
  {
    $images = CollectionEmbedField::create('items_image', ['entry' => ['mapper' => $imageMapper = new FormMapper('image')]]);
    $texts = CollectionEmbedField::create('items_text', ['entry' => ['mapper' => $textMapper = new FormMapper('text')]]);
    $field = CollectionEmbedField::create('items', ['entry' => ['mapper' => $mapper = new FormMapper('item')]])
      ->addVariant('image', $images)
      ->addVariant('text', $texts);

    self::assertTrue($field->hasVariants());
    self::assertSame(['image' => $images, 'text' => $texts], $field->getVariants());
    self::assertSame([$mapper, $imageMapper, $textMapper], $field->getMappers());
    self::assertSame(['text' => $texts], $field->setVariants(['text' => $texts])->getVariants());
  }

  public function testGroupedChoices(): void
  {
    $field = CollectionEmbedField::create('items', ['collections' => ['choices' => [
      'items_image' => ['entitled' => 'Image', 'category' => 'media'],
      'items_text' => ['entitled' => 'Text'],
      'items_video' => ['entitled' => 'Video', 'category' => 'media'],
    ]]]);

    self::assertSame([
      '' => ['items_text' => ['entitled' => 'Text']],
      'media' => ['items_image' => ['entitled' => 'Image', 'category' => 'media'], 'items_video' => ['entitled' => 'Video', 'category' => 'media']],
    ], $field->getGroupedChoices());
  }

  public function testWidget(): void
  {
    $widget = CollectionEmbedField::create('items', [
      'title' => 'Items',
      'button' => 'button.new.item',
      'color' => 2,
      'allow' => ['child' => true],
      'children_parameters' => ['max' => 5],
      'master_children' => true,
      'between_insert' => true,
      'sortable' => ['editable' => true],
    ])->getPresentation()['widget'];

    self::assertSame('Items', $widget['title']);
    self::assertSame('button.new.item', $widget['button']);
    self::assertSame(2, $widget['color']);
    self::assertSame(['child' => true, 'add' => true, 'delete' => true], $widget['allow']);
    self::assertSame(['min' => null, 'max' => 5], $widget['children_parameters']);
    self::assertTrue($widget['master_children']);
    self::assertFalse($widget['view_position']);
    self::assertTrue($widget['between_insert']);
    self::assertTrue($widget['sortable']);
    self::assertSame('__name__', $widget['prototype_name']);
  }

  public function testUnknownNestedOptionRejected(): void
  {
    $this->expectException(UndefinedOptionsException::class);

    CollectionEmbedField::create('items', ['entry' => ['formMapperKey' => 'items']]);
  }

  public function testCanBeExtended(): void
  {
    $field = new class('contentBlocks') extends CollectionEmbedField {};

    self::assertSame(CollectionType::class, $field->getSymfonyType());
  }
}
