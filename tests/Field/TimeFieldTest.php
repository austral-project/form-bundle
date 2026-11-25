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

use Austral\FormBundle\Field\TimeField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

final class TimeFieldTest extends TestCase
{
  public function testClockPickerByDefault(): void
  {
    $field = TimeField::create('openingAt', ['attr' => ['class' => 'time']]);
    $options = $field->getSymfonyOptions();
    $config = json_decode($options['attr']['data-clockpicker-options'], true, 512, \JSON_THROW_ON_ERROR);

    self::assertSame(TimeType::class, $field->getSymfonyType());
    self::assertSame('single_text', $options['widget']);
    self::assertFalse($options['with_seconds']);
    self::assertFalse($options['html5']);
    self::assertSame('time timepicker-ui-input', $options['attr']['class']);
    self::assertTrue($options['attr']['data-clockpicker']);
    self::assertSame(['clockType' => '24h', 'switchToMinutesAfterSelectHour' => true, 'enableScrollbar' => false], $config);
    self::assertSame('austral-picto-clock', $field->getPresentation()['picto']);
  }

  public function testWithoutPicker(): void
  {
    $options = TimeField::create('openingAt', ['picker' => false])->getSymfonyOptions();

    self::assertSame('single_text', $options['widget']);
    self::assertArrayNotHasKey('data-clockpicker', $options['attr']);
    self::assertArrayNotHasKey('html5', $options);
  }
}
