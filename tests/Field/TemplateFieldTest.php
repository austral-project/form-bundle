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

use Austral\FormBundle\Field\TemplateField;
use PHPUnit\Framework\TestCase;

final class TemplateFieldTest extends TestCase
{
  public function testDisplayOnly(): void
  {
    $field = TemplateField::create('information', '@AustralSeo/information.html.twig', ['size' => 'col--6'], ['level' => 'info']);

    self::assertTrue($field->isDisplayOnly());
    self::assertNull($field->getSymfonyType());
    self::assertSame('@AustralSeo/information.html.twig', $field->getTemplate());
    self::assertSame(['level' => 'info'], $field->getTemplateVars());
    self::assertSame('col--6', $field->getSize());
    self::assertSame('templateField', $field->getPresentation()['field_class']);
  }

  public function testVarsFromTheOptions(): void
  {
    self::assertSame(['a' => 1], TemplateField::create('information', 'info.html.twig', ['template_vars' => ['a' => 1]])->getTemplateVars());
    self::assertSame(['b' => 2], TemplateField::create('information', 'info.html.twig', ['template_vars' => ['a' => 1]], ['b' => 2])->getTemplateVars());
  }
}
