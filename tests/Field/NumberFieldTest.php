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

use Austral\FormBundle\Field\NumberField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

final class NumberFieldTest extends TestCase
{
  public function testNumberType(): void
  {
    $field = NumberField::create('rating', ['form_options' => ['scale' => 2]]);

    self::assertFalse($field->isInteger());
    self::assertSame(NumberType::class, $field->getSymfonyType());
    self::assertSame(2, $field->getSymfonyOptions()['scale']);
  }

  public function testIntegerType(): void
  {
    self::assertSame(IntegerType::class, NumberField::create('views', ['integer' => true])->getSymfonyType());
    self::assertSame(IntegerType::class, NumberField::create('views')->addOption('integer', true)->getSymfonyType());
  }
}
