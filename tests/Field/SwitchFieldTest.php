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

use Austral\FormBundle\Field\SwitchField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

final class SwitchFieldTest extends TestCase
{
  public function testDefaults(): void
  {
    $field = SwitchField::create('isPublished');

    self::assertSame(CheckboxType::class, $field->getSymfonyType());
    self::assertSame(['autocomplete' => 'off'], $field->getSymfonyOptions()['attr']);
    self::assertSame([
      'appearance' => SwitchField::APPEARANCE_SWITCH,
      'title' => null,
      'summary' => null,
      'pictos' => ['enabled' => null, 'disabled' => null],
      'style' => '--switch-button-color-enabled:var(--color-white-force); --switch-button-color-disabled:var(--color-white-force); --switch-fond-color-enabled:var(--color-green-40); --switch-fond-color-disabled:var(--color-main-40);',
    ], $field->getPresentation()['widget']);
  }

  public function testAutocompleteCanBeChanged(): void
  {
    self::assertSame('on', SwitchField::create('isPublished', ['attr' => ['autocomplete' => 'on']])->getSymfonyOptions()['attr']['autocomplete']);
  }

  public function testWidget(): void
  {
    $widget = SwitchField::create('isPublished', [
      'appearance' => SwitchField::APPEARANCE_CHECKBOX,
      'title' => 'fields.isPublished.title',
      'summary' => 'fields.isPublished.summary',
      'pictos' => ['enabled' => 'austral-picto-check'],
      'button_colors' => ['enabled' => 'red', 'disabled' => null],
      'background_colors' => ['disabled' => 'blue'],
    ])->getPresentation()['widget'];

    self::assertSame(SwitchField::APPEARANCE_CHECKBOX, $widget['appearance']);
    self::assertSame('fields.isPublished.title', $widget['title']);
    self::assertSame('fields.isPublished.summary', $widget['summary']);
    self::assertSame(['enabled' => 'austral-picto-check', 'disabled' => null], $widget['pictos']);
    self::assertSame('--switch-button-color-enabled:red; --switch-fond-color-enabled:var(--color-green-40); --switch-fond-color-disabled:blue;', $widget['style']);
  }

  public function testNestedOptionsMergedBySetOptions(): void
  {
    $field = SwitchField::create('isPublished', ['pictos' => ['enabled' => 'on']])->setOptions(['title' => 'Title']);

    self::assertSame(['enabled' => 'on', 'disabled' => null], $field->getOption('pictos'));
    self::assertSame('Title', $field->getOption('title'));
  }

  public function testUnknownNestedOptionRejected(): void
  {
    $this->expectException(UndefinedOptionsException::class);

    SwitchField::create('isPublished', ['pictos' => ['active' => 'on']]);
  }

  public function testUnknownAppearanceRejected(): void
  {
    $this->expectException(InvalidOptionsException::class);

    SwitchField::create('isPublished', ['appearance' => 'toggle']);
  }
}
