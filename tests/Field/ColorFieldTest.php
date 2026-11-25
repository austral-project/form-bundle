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

use Austral\FormBundle\Field\ColorField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class ColorFieldTest extends TestCase
{
  public function testPickerConfiguration(): void
  {
    $field = ColorField::create('color', ['colorpicker_options' => ['swatches' => null, 'components' => ['hue' => true], 'interaction' => ['hex' => true]]]);
    $attr = $field->getSymfonyOptions()['attr'];
    $config = json_decode($attr['data-colorpicker-options'], true, 512, \JSON_THROW_ON_ERROR);

    self::assertSame(TextType::class, $field->getSymfonyType());
    self::assertTrue($attr['data-colorpicker']);
    self::assertSame('#colorPicker-color', $attr['data-colorpicker-element']);
    self::assertSame(['preview' => true, 'components' => ['palette' => false, 'opacity' => false, 'hue' => true], 'interaction' => ['hex' => true, 'rgba' => false, 'hsla' => false, 'hsva' => false, 'cmyk' => false, 'input' => false, 'clear' => false, 'save' => false]], $config);
  }

  public function testDefaultSwatches(): void
  {
    $config = json_decode(ColorField::create('color')->getSymfonyOptions()['attr']['data-colorpicker-options'], true, 512, \JSON_THROW_ON_ERROR);

    self::assertIsArray($config);
    self::assertCount(64, $config['swatches']);
  }
}
