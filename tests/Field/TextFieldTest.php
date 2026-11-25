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

use Austral\FormBundle\Field\TextField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class TextFieldTest extends TestCase
{
  public function testTextType(): void
  {
    $field = TextField::create('title', ['placeholder' => 'Title']);

    self::assertSame('title', $field->getName());
    self::assertSame(TextType::class, $field->getSymfonyType());
    self::assertSame(['placeholder' => 'Title'], $field->getSymfonyOptions()['attr']);
    self::assertSame('textField', $field->getPresentation()['field_class']);
    self::assertSame([], $field->getPresentation()['widget']);
  }

  public function testCanBeExtended(): void
  {
    $field = new class('path') extends TextField {};

    self::assertSame(TextType::class, $field->getSymfonyType());
  }
}
