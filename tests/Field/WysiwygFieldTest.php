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

use Austral\FormBundle\Field\WysiwygField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class WysiwygFieldTest extends TestCase
{
  public function testEditorConfiguration(): void
  {
    $field = WysiwygField::create('content', ['wysiwyg_options' => ['minHeight' => 400, 'toolbar' => [['style', ['bold']]]]]);
    $attr = $field->getSymfonyOptions()['attr'];
    $config = json_decode($attr['data-wysiwyg-options'], true, 512, \JSON_THROW_ON_ERROR);

    self::assertSame(TextareaType::class, $field->getSymfonyType());
    self::assertTrue($attr['data-wysiwyg']);
    self::assertIsArray($config);
    self::assertSame(400, $config['minHeight']);
    self::assertSame([['style', ['bold']]], $config['toolbar']);
    self::assertTrue($config['autoLink']);
    self::assertSame('austral-picto-bold', $config['icons']['bold']);
  }
}
