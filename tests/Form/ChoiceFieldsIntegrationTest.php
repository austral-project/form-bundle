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
use Austral\FormBundle\Field\ChoiceField;
use Austral\FormBundle\Field\SelectField;
use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\ArticleStatus;
use Austral\FormBundle\Tests\Fixtures\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

/**
 * Select and choice fields in a real form, tags included.
 */
final class ChoiceFieldsIntegrationTest extends TestCase
{
  public function testSelect(): void
  {
    $product = new Product();

    $form = $this->form($product, SelectField::create('category', ['Chairs' => 'chairs', 'Tables' => 'tables']));
    $form->submit(['category' => 'tables']);

    self::assertTrue($form->isValid());
    self::assertSame('tables', $product->category);
  }

  public function testSelectRejectsAnUnknownValueWithoutTags(): void
  {
    $form = $this->form(new Product(), SelectField::create('category', ['Chairs' => 'chairs']));
    $form->submit(['category' => 'sofas']);

    self::assertFalse($form->isValid());
  }

  public function testTagsAcceptNewValues(): void
  {
    $product = new Product();

    $form = $this->form($product, SelectField::create('tags', ['Wood' => 'wood'], ['multiple' => true, 'select_options' => ['tag' => true]]));
    $form->submit(['tags' => ['wood', 'oak', 'outdoor']]);

    self::assertTrue($form->isValid());
    self::assertSame(['wood', 'oak', 'outdoor'], $product->tags);
  }

  public function testTagsShowTheCurrentValues(): void
  {
    $product = new Product();
    $product->tags = ['oak'];

    $form = $this->form($product, SelectField::create('tags', ['Wood' => 'wood'], ['multiple' => true, 'select_options' => ['tag' => true]]));
    $choices = array_map(static fn ($choice): string => $choice->value, $form->createView()['tags']->vars['choices']);

    self::assertSame(['wood', 'oak'], array_values($choices));
    self::assertSame(['oak'], $form->get('tags')->getData());
  }

  public function testTagChoicesFromAClosure(): void
  {
    $field = SelectField::create('tags', [], [
      'multiple' => true,
      'select_options' => ['tag' => true],
      'tag_choices' => static fn (Product $product): array => ['Suggested' => 'suggested'],
    ]);

    $view = $this->form(new Product(), $field)->createView();

    self::assertSame('Suggested', $view['tags']->vars['choices'][0]->label);
  }

  public function testTagsKeepTheFieldOptions(): void
  {
    $field = SelectField::create('tags', [], ['multiple' => true, 'select_options' => ['tag' => true], 'picto' => 'austral-picto-tag']);

    $form = $this->form(new Product(), $field);
    $form->submit(['tags' => ['oak']]);

    $view = $form->createView()['tags'];
    self::assertSame('austral-picto-tag', $view->vars['austral']['picto']);
    self::assertTrue($view->vars['multiple']);
    self::assertTrue($view->vars['attr']['data-select']);
  }

  public function testEnum(): void
  {
    $product = new Product();

    $form = $this->form($product, SelectField::create('status', [], ['enum' => ArticleStatus::class]));
    $form->submit(['status' => 'published']);

    self::assertTrue($form->isValid());
    self::assertSame(ArticleStatus::Published, $product->status);
  }

  public function testChoiceRadios(): void
  {
    $product = new Product();

    $form = $this->form($product, ChoiceField::create('published', ['choices.yes' => true, 'choices.no' => false]));
    $view = $form->createView();
    $form->submit(['published' => '1']);

    self::assertTrue($view['published']->vars['expanded']);
    self::assertCount(2, $view['published']->children);
    self::assertSame('field_choices_yes', $view['published']->children[0]->vars['attr']['class']);
    self::assertTrue($form->isValid());
    self::assertTrue($product->published);
  }

  /**
   * @param Product                 $product
   * @param SelectField|ChoiceField $field
   *
   * @return FormInterface<mixed>
   */
  private function form(Product $product, SelectField|ChoiceField $field): FormInterface
  {
    $mapper = (new FormMapper('form_austral', Product::class))->add($field);

    return Forms::createFormFactoryBuilder()
      ->addExtension(new ValidatorExtension(Validation::createValidator()))
      ->addType(new AustralFormType(new DoctrineConstraintGuesser()))
      ->addTypeExtension(new PresentationTypeExtension())
      ->getFormFactory()
      ->createNamed($mapper->getName(), AustralFormType::class, $product, ['mapper' => $mapper]);
  }
}
