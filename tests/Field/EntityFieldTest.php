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

use Austral\FormBundle\Field\EntityField;
use Austral\FormBundle\Tests\Fixtures\Article;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class EntityFieldTest extends TestCase
{
  public function testEntityType(): void
  {
    $field = EntityField::create('article', Article::class, ['class' => 'large']);
    $options = $field->getSymfonyOptions();

    self::assertSame(EntityType::class, $field->getSymfonyType());
    self::assertSame(Article::class, $field->getEntityClass());
    self::assertSame(Article::class, $options['class']);
    self::assertSame('large', $field->getPresentation()['class']);
    self::assertArrayNotHasKey('choices', $options);
    self::assertArrayNotHasKey('query_builder', $options);
    self::assertArrayNotHasKey('choice_label', $options);
    self::assertTrue($options['attr']['data-select']);
  }

  public function testQueryBuilderAndChoiceLabel(): void
  {
    $queryBuilder = static fn (): null => null;
    $options = EntityField::create('article', Article::class, ['query_builder' => $queryBuilder, 'choice_label' => 'title', 'multiple' => true])->getSymfonyOptions();

    self::assertSame($queryBuilder, $options['query_builder']);
    self::assertSame('title', $options['choice_label']);
    self::assertTrue($options['multiple']);
  }
}
