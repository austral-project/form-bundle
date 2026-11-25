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

use Austral\FormBundle\Field\ChoiceField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

final class ChoiceFieldTest extends TestCase
{
  public function testRadios(): void
  {
    $field = ChoiceField::create('isPublished', ['choices.yes' => true, 'choices.no' => false]);
    $options = $field->getSymfonyOptions();

    self::assertSame(ChoiceType::class, $field->getSymfonyType());
    self::assertSame(['choices.yes' => true, 'choices.no' => false], $options['choices']);
    self::assertTrue($options['expanded']);
    self::assertFalse($options['multiple']);
    self::assertFalse($options['placeholder']);
    self::assertSame(['class' => 'field_choices_yes', 'autocomplete' => 'off'], $options['choice_attr'](true, 'choices.yes'));
  }

  public function testCheckboxes(): void
  {
    self::assertTrue(ChoiceField::create('days', ['Monday' => 1], ['multiple' => true])->getSymfonyOptions()['multiple']);
  }

  public function testDefaultStylesByValue(): void
  {
    $styles = ChoiceField::create('isPublished', ['yes' => true, 'no' => false])->getStylesByChoices();

    self::assertSame('--element-choice-current-background:var(--color-green-20)', $styles['yes'][0]);
    self::assertSame('--element-choice-current-background:var(--color-main-20)', $styles['no'][0]);
  }

  public function testStylesOfAChoiceThenOfTheField(): void
  {
    $field = ChoiceField::create('status', [
      'draft' => ['value' => 'draft', 'styles' => ['--color:grey']],
      'published' => 'published',
    ], ['choices_styles' => ['--color:blue']]);

    self::assertSame(['draft' => ['--color:grey'], 'published' => ['--color:blue']], $field->getStylesByChoices());
    self::assertSame(['draft' => 'draft', 'published' => 'published'], $field->getChoices());
  }

  public function testWidget(): void
  {
    $widget = ChoiceField::create('status', ['a' => 'a'], ['direction' => ChoiceField::DIRECTION_VERTICAL, 'choice_style' => 'light'])->getPresentation()['widget'];

    self::assertFalse($widget['multiple']);
    self::assertSame('vertical', $widget['direction']);
    self::assertSame('light', $widget['choice_style']);
    self::assertArrayHasKey('a', $widget['styles']);
  }

  public function testChoiceAttrCanBeReplaced(): void
  {
    $choiceAttr = static fn (): array => [];

    self::assertSame($choiceAttr, ChoiceField::create('status', ['a' => 'a'], ['form_options' => ['choice_attr' => $choiceAttr]])->getSymfonyOptions()['choice_attr']);
  }

  public function testUnknownChoiceKeyRejected(): void
  {
    $this->expectException(\InvalidArgumentException::class);

    ChoiceField::create('status', ['a' => ['value' => 'a', 'color' => 'red']]);
  }

  public function testUnknownDirectionRejected(): void
  {
    $this->expectException(InvalidOptionsException::class);

    ChoiceField::create('status', ['a' => 'a'], ['direction' => 'diagonal']);
  }
}
