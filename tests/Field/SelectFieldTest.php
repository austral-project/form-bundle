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

use Austral\FormBundle\Field\AbstractSelectField;
use Austral\FormBundle\Field\SelectField;
use Austral\FormBundle\Tests\Fixtures\ArticleStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

final class SelectFieldTest extends TestCase
{
  public function testChoiceType(): void
  {
    $field = SelectField::create('status', ['Draft' => 'draft', 'Published' => 'published']);
    $options = $field->getSymfonyOptions();

    self::assertSame(ChoiceType::class, $field->getSymfonyType());
    self::assertSame(['Draft' => 'draft', 'Published' => 'published'], $options['choices']);
    self::assertFalse($options['multiple']);
    self::assertSame('off', $options['attr']['autocomplete']);
    self::assertTrue($options['attr']['data-select']);
    self::assertArrayNotHasKey('data-null-value', $options['attr']);
  }

  public function testChoicesJsConfiguration(): void
  {
    $options = SelectField::create('status', [], ['select_options' => ['searchEnabled' => false]])->getSymfonyOptions();
    $config = json_decode($options['attr']['data-select-options'], true, 512, \JSON_THROW_ON_ERROR);

    self::assertIsArray($config);
    self::assertFalse($config['searchEnabled']);
    self::assertTrue($config['enabled']);
    self::assertFalse($config['tag']);
    self::assertSame(10, $config['searchResultLimit']);
    self::assertSame(', ', $config['delimiter']);
    self::assertArrayNotHasKey('removeItemButton', $config);
  }

  public function testMultipleAddsTheRemoveButton(): void
  {
    $field = SelectField::create('tags', [], ['multiple' => true]);

    self::assertTrue($field->isMultiple());
    self::assertTrue($field->getSymfonyOptions()['multiple']);
    self::assertTrue($field->getSelectOptions()['removeItemButton']);
  }

  public function testWithoutChoicesJs(): void
  {
    $options = SelectField::create('status', [], ['select_options' => ['enabled' => false]])->getSymfonyOptions();

    self::assertFalse($options['attr']['data-select']);
    self::assertArrayNotHasKey('data-select-options', $options['attr']);
  }

  public function testExpandedHasNoSelect(): void
  {
    $options = SelectField::create('status', [], ['form_options' => ['expanded' => true]])->getSymfonyOptions();

    self::assertArrayNotHasKey('data-select', $options['attr']);
  }

  public function testNullValue(): void
  {
    self::assertSame('true', SelectField::create('status', [], ['null_value' => true])->getSymfonyOptions()['attr']['data-null-value']);
  }

  public function testFormOptionsWin(): void
  {
    $options = SelectField::create('status', ['A' => 'a'], ['form_options' => ['choices' => ['B' => 'b'], 'multiple' => true]])->getSymfonyOptions();

    self::assertSame(['B' => 'b'], $options['choices']);
    self::assertTrue($options['multiple']);
  }

  public function testSetChoices(): void
  {
    $field = SelectField::create('status')->setChoices(['A' => 'a']);

    self::assertSame(['A' => 'a'], $field->getChoices());
    self::assertSame(['A' => 'a'], $field->getSymfonyOptions()['choices']);
  }

  public function testEnum(): void
  {
    $field = SelectField::create('status', [], ['enum' => ArticleStatus::class]);
    $options = $field->getSymfonyOptions();

    self::assertSame(EnumType::class, $field->getSymfonyType());
    self::assertSame(ArticleStatus::class, $options['class']);
    self::assertArrayNotHasKey('choices', $options);
  }

  public function testNotAnEnumRejected(): void
  {
    $this->expectException(InvalidOptionsException::class);

    SelectField::create('status', [], ['enum' => \stdClass::class]);
  }

  public function testWidget(): void
  {
    $widget = SelectField::create('tags', [], [
      'multiple' => true,
      'select_options' => ['tag' => true],
      'theme' => ['name' => AbstractSelectField::THEME_CHOICE_LIST, 'direction' => AbstractSelectField::DIRECTION_VERTICAL],
    ])->getPresentation()['widget'];

    self::assertSame(['multiple' => true, 'tags' => true, 'theme' => 'choiceList', 'direction' => 'vertical'], $widget);
    self::assertSame('selectField', SelectField::create('status')->getPresentation()['field_class']);
  }

  public function testUnknownChoicesJsKeyRejected(): void
  {
    $this->expectException(UndefinedOptionsException::class);

    SelectField::create('status', [], ['select_options' => ['search' => false]]);
  }

  public function testUnknownThemeRejected(): void
  {
    $this->expectException(InvalidOptionsException::class);

    SelectField::create('status', [], ['theme' => ['name' => 'list']]);
  }
}
