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
use Austral\FormBundle\Field\NumberField;
use Austral\FormBundle\Field\SwitchField;
use Austral\FormBundle\Field\SymfonyField;
use Austral\FormBundle\Field\TemplateField;
use Austral\FormBundle\Field\TextareaField;
use Austral\FormBundle\Field\TextField;
use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

/**
 * The fields of the first batch in a real form: built, submitted, exposed to the theme.
 */
final class FieldsIntegrationTest extends TestCase
{
  public function testSubmit(): void
  {
    $product = new Product();

    $form = $this->form($product);
    $form->submit([
      'name' => 'Chair',
      'description' => 'Wooden chair',
      'price' => '12.5',
      'stock' => '3',
      'enabled' => '1',
      'reference' => 'CH-1',
    ]);

    self::assertTrue($form->isSynchronized());
    self::assertTrue($form->isValid());
    self::assertSame('Chair', $product->name);
    self::assertSame('Wooden chair', $product->description);
    self::assertSame(12.5, $product->price);
    self::assertSame(3, $product->stock);
    self::assertTrue($product->enabled);
    self::assertSame('CH-1', $product->reference);
  }

  public function testUncheckedSwitch(): void
  {
    $product = new Product();
    $product->enabled = true;

    $this->form($product)->submit(['name' => 'Chair']);

    self::assertFalse($product->enabled);
  }

  public function testTemplateIsNotPartOfTheFormButOfTheLayout(): void
  {
    $form = $this->form(new Product());

    self::assertFalse($form->has('help'));
    $children = $form->createView()->vars['austral_layout'][0]['children'];
    self::assertSame(['name', 'description', 'price', 'stock', 'enabled', 'reference'], array_values(array_filter($children, 'is_string')));
    $template = array_values(array_filter($children, 'is_array'))[0];
    self::assertSame('template', $template['type']);
    self::assertSame('help.html.twig', $template['template']);
  }

  public function testViewOfTheFields(): void
  {
    $view = $this->form(new Product())->createView();

    self::assertSame('textField', $view['name']->vars['austral']['field_class']);
    self::assertSame('middle', $view['description']->vars['attr']['class']);
    self::assertSame('Enabled', $view['enabled']->vars['austral']['widget']['title']);
    self::assertSame('off', $view['enabled']->vars['attr']['autocomplete']);
    self::assertContains('hidden', $view['reference']->vars['block_prefixes']);
  }

  /**
   * @param Product $product
   *
   * @return FormInterface<mixed>
   */
  private function form(Product $product): FormInterface
  {
    $mapper = new FormMapper('form_austral', Product::class)
      ->add(TextField::create('name', ['required' => true]))
      ->add(TextareaField::create('description'))
      ->add(NumberField::create('price'))
      ->add(NumberField::create('stock', ['integer' => true]))
      ->add(SwitchField::create('enabled', ['title' => 'Enabled']))
      ->add(SymfonyField::create('reference', HiddenType::class))
      ->add(TemplateField::create('help', 'help.html.twig'));

    return Forms::createFormFactoryBuilder()
      ->addExtension(new ValidatorExtension(Validation::createValidator()))
      ->addType(new AustralFormType(new DoctrineConstraintGuesser()))
      ->addTypeExtension(new PresentationTypeExtension())
      ->getFormFactory()
      ->createNamed($mapper->getName(), AustralFormType::class, $product, ['mapper' => $mapper]);
  }
}
