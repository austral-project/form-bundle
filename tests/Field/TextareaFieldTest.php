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

use Austral\FormBundle\Field\TextareaField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

final class TextareaFieldTest extends TestCase
{
  public function testMiddleByDefault(): void
  {
    $field = TextareaField::create('summary');

    self::assertSame(TextareaType::class, $field->getSymfonyType());
    self::assertSame(TextareaField::HEIGHT_MIDDLE, $field->getHeight());
    self::assertSame(['class' => 'middle'], $field->getSymfonyOptions()['attr']);
    self::assertSame(TextareaField::HEIGHT_MIDDLE, TextareaField::create('summary', null)->getHeight());
  }

  public function testHeightAddedToTheClassOfTheTextarea(): void
  {
    $field = TextareaField::create('summary', TextareaField::HEIGHT_BIG, ['attr' => ['class' => 'editor']]);

    self::assertSame(['class' => 'editor big'], $field->getSymfonyOptions()['attr']);
  }

  public function testAutoHeightHasTwoRows(): void
  {
    self::assertSame(['class' => 'auto', 'rows' => 2], TextareaField::create('summary', TextareaField::HEIGHT_AUTO)->getSymfonyOptions()['attr']);
    self::assertSame(5, TextareaField::create('summary', TextareaField::HEIGHT_AUTO, ['attr' => ['rows' => 5]])->getSymfonyOptions()['attr']['rows']);
  }

  public function testSetHeight(): void
  {
    $field = TextareaField::create('summary')->setHeight(TextareaField::HEIGHT_BIG);

    self::assertSame(TextareaField::HEIGHT_BIG, $field->getHeight());
  }

  public function testUnknownHeightRejected(): void
  {
    $this->expectException(InvalidOptionsException::class);

    TextareaField::create('summary', 'huge');
  }
}
