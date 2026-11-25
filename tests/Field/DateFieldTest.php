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

use Austral\FormBundle\Field\DateField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\DateType;

final class DateFieldTest extends TestCase
{
  public function testSingleTextWithPicker(): void
  {
    $field = DateField::create('publishedAt');
    $options = $field->getSymfonyOptions();
    $config = json_decode($options['attr']['data-datepicker-options'], true, 512, \JSON_THROW_ON_ERROR);

    self::assertSame(DateType::class, $field->getSymfonyType());
    self::assertSame('single_text', $options['widget']);
    self::assertSame('dd/MM/yyyy', $options['format']);
    self::assertFalse($options['html5']);
    self::assertTrue($options['attr']['data-datepicker']);
    self::assertIsArray($config);
    self::assertSame('dd/mm/yyyy', $config['format']);
    self::assertSame(1, $config['weekStart']);
    self::assertSame('austral-picto-calendar', $field->getPresentation()['picto']);
  }

  public function testFormatFollowsTheDateFormat(): void
  {
    $field = DateField::create('publishedAt', ['date_format' => 'yyyy-MM-dd', 'datepicker_options' => ['language' => 'fr']]);
    $config = json_decode($field->getSymfonyOptions()['attr']['data-datepicker-options'], true, 512, \JSON_THROW_ON_ERROR);

    self::assertIsArray($config);
    self::assertSame('yyyy-mm-dd', $config['format']);
    self::assertSame('fr', $config['language']);
    self::assertSame('yyyy-MM-dd', $field->getSymfonyOptions()['format']);
  }

  public function testPickerFormatCanBeGiven(): void
  {
    $config = json_decode(DateField::create('publishedAt', ['datepicker_options' => ['format' => 'd/m/Y']])->getSymfonyOptions()['attr']['data-datepicker-options'], true, 512, \JSON_THROW_ON_ERROR);

    self::assertIsArray($config);
    self::assertSame('d/m/Y', $config['format']);
  }
}
