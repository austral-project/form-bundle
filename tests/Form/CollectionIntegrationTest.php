<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Form;

use Austral\FormBundle\Constraint\DoctrineConstraintGuesser;
use Austral\FormBundle\Field\CollectionEmbedField;
use Austral\FormBundle\Field\TextField;
use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Layout\Fieldset;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\Gallery;
use Austral\FormBundle\Tests\Fixtures\GalleryItem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

/**
 * CollectionEmbedField in a real form: entries built from a mapper, their layout, variants.
 */
final class CollectionIntegrationTest extends TestCase
{
  public function testEntriesAreAustralForms(): void
  {
    $gallery = new Gallery();
    $gallery->items = [new GalleryItem('First', 1), new GalleryItem('Second', 2)];

    $view = $this->form($gallery, $this->itemsField())->createView();
    $first = $view['items'][0];

    self::assertSame('form_austral[items][0][label]', $first['label']->vars['full_name']);
    self::assertSame('First', $first['label']->vars['value']);
    self::assertSame('textField', $first['label']->vars['austral']['field_class']);
    self::assertSame('form_austral_items_0-fieldset-item', $first->vars['austral_layout'][0]['id']);
    self::assertSame('form_austral_items_1-fieldset-item', $view['items'][1]->vars['austral_layout'][0]['id']);
  }

  public function testLabelParametersOfEachEntry(): void
  {
    $gallery = new Gallery();
    $gallery->items = [new GalleryItem('First', 1), new GalleryItem('Second', 2)];

    $view = $this->form($gallery, $this->itemsField())->createView();

    self::assertSame(['%label%' => 'First'], $view['items'][0]->vars['austral_layout'][0]['label_parameters']);
    self::assertSame(['%label%' => 'Second'], $view['items'][1]->vars['austral_layout'][0]['label_parameters']);
  }

  public function testPositionOfEachEntry(): void
  {
    $gallery = new Gallery();
    $gallery->items = [new GalleryItem('First', 1), new GalleryItem('Second', 12)];

    $view = $this->form($gallery, $this->itemsField())->createView();

    self::assertSame(['sortable' => '01'], $view['items'][0]->vars['austral_entry']);
    self::assertSame(['sortable' => '12'], $view['items'][1]->vars['austral_entry']);
  }

  public function testPrototype(): void
  {
    $prototype = $this->form(new Gallery(), $this->itemsField())->createView()['items']->vars['prototype'];

    self::assertSame('form_austral[items][__name__][label]', $prototype['label']->vars['full_name']);
    self::assertSame('form_austral_items___name__-fieldset-item', $prototype->vars['austral_layout'][0]['id']);
    self::assertSame(['sortable' => ''], $prototype->vars['austral_entry']);
  }

  public function testNoValidateOnTheFormOnly(): void
  {
    $gallery = new Gallery();
    $gallery->items = [new GalleryItem('First', 1)];

    $view = $this->form($gallery, $this->itemsField())->createView();

    self::assertSame('novalidate', $view->vars['attr']['novalidate']);
    self::assertArrayNotHasKey('novalidate', $view['items'][0]->vars['attr']);
  }

  public function testSubmitAddsUpdatesAndRemovesEntries(): void
  {
    $gallery = new Gallery();
    $first = new GalleryItem('First', 1);
    $gallery->items = [0 => $first, 1 => new GalleryItem('Second', 2)];

    $form = $this->form($gallery, $this->itemsField());
    $form->submit(['items' => [
      0 => ['label' => 'First changed', 'position' => '1'],
      2 => ['label' => 'Third', 'position' => '3'],
    ]]);

    $items = self::items($gallery);
    self::assertTrue($form->isValid());
    self::assertSame(['First changed', 'Third'], array_map(static fn (GalleryItem $item): ?string => $item->label, $items));
    self::assertSame($first, $items[0]);
    self::assertSame(3, $items[1]->position);
  }

  public function testEntryConstraintsFromTheEntryMapper(): void
  {
    $gallery = new Gallery();

    $form = $this->form($gallery, $this->itemsField(['required' => true]));
    $form->submit(['items' => [['label' => '', 'position' => '1']]]);

    self::assertFalse($form->isValid());
    self::assertSame('errors.not_null', $form->getErrors(true)[0]->getMessageTemplate());
  }

  public function testVariants(): void
  {
    $gallery = new Gallery();
    $gallery->items = [new GalleryItem('Photo', 1, 'image'), new GalleryItem('Intro', 2, 'text')];
    $field = CollectionEmbedField::create('items', ['collections' => ['choices' => ['items_image' => ['entitled' => 'Image'], 'items_text' => ['entitled' => 'Text']]]])
      ->addVariant('items_image', $this->variant('image'))
      ->addVariant('items_text', $this->variant('text'));

    $form = $this->form($gallery, $field);

    self::assertFalse($form->has('items'));
    self::assertTrue($form->has('items_image'));
    self::assertTrue($form->has('items_text'));
    self::assertSame('Photo', $form->get('items_image')->get('0')->get('label')->getData());

    $node = $form->createView()->vars['austral_layout'][0]['children'][0];
    self::assertSame('variants', $node['type']);
    self::assertSame('form_austral-variants-items', $node['id']);
    self::assertSame(['items_image', 'items_text'], $node['children']);
    self::assertSame(['' => ['items_image' => ['entitled' => 'Image'], 'items_text' => ['entitled' => 'Text']]], $node['austral']['widget']['choices']);
  }

  public function testVariantsSubmittedThroughTheirSetters(): void
  {
    $gallery = new Gallery();
    $gallery->items = [new GalleryItem('Photo', 1, 'image'), new GalleryItem('Intro', 2, 'text')];
    $field = CollectionEmbedField::create('items')
      ->addVariant('items_image', $this->variant('image'))
      ->addVariant('items_text', $this->variant('text'));

    $form = $this->form($gallery, $field);
    $form->submit([
      'items_image' => [0 => ['label' => 'Photo', 'position' => '1'], 1 => ['label' => 'Second photo', 'position' => '3']],
      'items_text' => [],
    ]);

    self::assertTrue($form->isValid());
    self::assertSame(['Photo', 'Second photo'], array_map(static fn (GalleryItem $item): ?string => $item->label, $gallery->getItemsByType('image')));
    self::assertSame([], $gallery->getItemsByType('text'));
  }

  /**
   * @param Gallery $gallery
   *
   * @return list<GalleryItem>
   */
  private static function items(Gallery $gallery): array
  {
    return array_values($gallery->items);
  }

  /**
   * @param array<string, mixed> $labelOptions
   *
   * @return CollectionEmbedField
   */
  private function itemsField(array $labelOptions = []): CollectionEmbedField
  {
    $mapper = new FormMapper('item', GalleryItem::class);
    $mapper->addFieldset('item', 'fieldsets.item')
      ->setLabelParametersResolver(static fn (Fieldset $fieldset, ?object $item): array => ['%label%' => $item instanceof GalleryItem ? $item->label : null])
      ->add(TextField::create('label', $labelOptions))
      ->add(TextField::create('position', ['form_options' => ['empty_data' => null]]));

    return CollectionEmbedField::create('items', [
      'entry' => ['mapper' => $mapper],
      'sortable' => ['value' => 'position', 'editable' => true],
      'form_options' => ['by_reference' => false],
    ]);
  }

  /**
   * @param string $type
   *
   * @return CollectionEmbedField
   */
  private function variant(string $type): CollectionEmbedField
  {
    $mapper = new FormMapper($type, GalleryItem::class)
      ->add(TextField::create('label'))
      ->add(TextField::create('position'));

    return CollectionEmbedField::create("items_{$type}", [
      'entry' => ['mapper' => $mapper],
      'getter' => static fn (Gallery $gallery): array => $gallery->getItemsByType($type),
      'setter' => static function (Gallery $gallery, array $items) use ($type): void {
        $gallery->setItemsByType($type, array_values($items));
      },
    ]);
  }

  /**
   * @param Gallery              $gallery
   * @param CollectionEmbedField $field
   *
   * @return FormInterface<mixed>
   */
  private function form(Gallery $gallery, CollectionEmbedField $field): FormInterface
  {
    $mapper = new FormMapper('form_austral', Gallery::class)->add($field);

    return Forms::createFormFactoryBuilder()
      ->addExtension(new ValidatorExtension(Validation::createValidator()))
      ->addType(new AustralFormType(new DoctrineConstraintGuesser()))
      ->addTypeExtension(new PresentationTypeExtension())
      ->getFormFactory()
      ->createNamed($mapper->getName(), AustralFormType::class, $gallery, ['mapper' => $mapper]);
  }
}
