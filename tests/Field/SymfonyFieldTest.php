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

use Austral\FormBundle\Field\SymfonyField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

final class SymfonyFieldTest extends TestCase
{
  public function testTypeByClass(): void
  {
    $field = SymfonyField::create('email', EmailType::class, ['form_options' => ['trim' => false]]);

    self::assertSame(EmailType::class, $field->getSymfonyType());
    self::assertFalse($field->getSymfonyOptions()['trim']);
  }

  public function testTypeByInstance(): void
  {
    self::assertSame(HiddenType::class, SymfonyField::create('latitude', new HiddenType())->getSymfonyType());
  }

  public function testNotAFormTypeRejected(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The type of the field "email"');

    SymfonyField::create('email', \stdClass::class);
  }
}
