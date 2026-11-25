<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Mapper;

use Austral\FormBundle\Layout\Fieldset;
use Austral\FormBundle\Layout\Group;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\TextLikeField;
use PHPUnit\Framework\TestCase;

final class FormMapperTest extends TestCase
{
  public function testDefaults(): void
  {
    $mapper = new FormMapper();

    self::assertSame('form', $mapper->getName());
    self::assertNull($mapper->getDataClass());
    self::assertNull($mapper->getObject());
    self::assertSame('form', $mapper->getTranslationDomain());
    self::assertNull($mapper->getValidationGroups());
    self::assertFalse($mapper->isHtml5Validate());
    self::assertSame([], $mapper->getFields());
    self::assertSame([], $mapper->getNodes());
  }

  public function testTheDataClassComesFromTheObject(): void
  {
    $object = new \ArrayObject();

    self::assertSame(\ArrayObject::class, (new FormMapper('form_austral', null, $object))->getDataClass());
    self::assertSame(\ArrayObject::class, (new FormMapper())->setObject($object)->getDataClass());
    self::assertSame(\stdClass::class, (new FormMapper('form', \stdClass::class))->setObject($object)->getDataClass());
  }

  public function testSetters(): void
  {
    $mapper = (new FormMapper())
      ->setName('form_austral')
      ->setDataClass(\stdClass::class)
      ->setTranslationDomain('austral')
      ->setValidationGroups(['Default', 'admin'])
      ->setHtml5Validate(true);

    self::assertSame('form_austral', $mapper->getName());
    self::assertSame(\stdClass::class, $mapper->getDataClass());
    self::assertSame('austral', $mapper->getTranslationDomain());
    self::assertSame(['Default', 'admin'], $mapper->getValidationGroups());
    self::assertTrue($mapper->isHtml5Validate());
  }

  public function testTheFluentWritingOfTheAdminClasses(): void
  {
    $mapper = new FormMapper();
    $result = $mapper
      ->addFieldset('generalInformation')
        ->add(TextLikeField::create('title'))
        ->addGroup('dates')
          ->setDirection(Group::DIRECTION_COLUMN)
          ->add(TextLikeField::create('start'))
          ->add(TextLikeField::create('end'))
        ->end()
      ->end()
      ->addFieldset('seo', null, Fieldset::POSITION_RIGHT)
        ->add(TextLikeField::create('refTitle'))
      ->end();

    self::assertSame($mapper, $result);
    self::assertSame(['title', 'start', 'end', 'refTitle'], array_keys($mapper->getFields()));
    self::assertCount(2, $mapper->getFieldsets());
  }

  public function testAddFieldsetReturnsTheFieldsetOfTheSameName(): void
  {
    $mapper = new FormMapper();

    self::assertSame($mapper->addFieldset('main'), $mapper->addFieldset('main'));
    self::assertCount(1, $mapper->getFieldsets());
  }

  public function testGetFieldsetFindsAFieldsetByItsName(): void
  {
    $mapper = new FormMapper();
    $fieldset = $mapper->addFieldset('fieldset.generalInformation');

    self::assertSame($fieldset, $mapper->getFieldset('fieldset.generalInformation'));
    self::assertNull($mapper->getFieldset('fieldset.unknown'));
  }

  /**
   * Regression: in 4.x before the rewrite, creating a modal changed the ids of the nodes created after it,
   * and getFieldset() never found anything.
   */
  public function testCreatingAModalDoesNotChangeTheIdsOfTheOtherNodes(): void
  {
    $mapper = new FormMapper();
    $mapper->addModal('editor');
    $fieldset = $mapper->addFieldset('fieldset.generalInformation');

    self::assertSame('fieldset-fieldset-generalinformation', $fieldset->getId());
    self::assertSame($fieldset, $mapper->getFieldset('fieldset.generalInformation'));
    self::assertSame('group-picto', $fieldset->addGroup('picto')->getId());
  }

  public function testFieldsAddedToTheMapperGoToTheDefaultFieldset(): void
  {
    $mapper = (new FormMapper())->add(TextLikeField::create('id'));
    $default = $mapper->getFieldset(FormMapper::DEFAULT_FIELDSET);

    self::assertInstanceOf(Fieldset::class, $default);
    self::assertSame(['id'], $default->getFieldNames());
    self::assertSame(Fieldset::POSITION_BOTTOM, $default->getPosition());
    self::assertFalse($default->isShowLabel());
    self::assertFalse($default->getLabel());
  }

  public function testAddsAndAddGroupOnTheMapperUseTheDefaultFieldset(): void
  {
    $mapper = (new FormMapper())->adds([TextLikeField::create('a'), null, TextLikeField::create('b')]);
    $group = $mapper->addGroup('group');

    self::assertSame(['a', 'b'], array_keys($mapper->getFields()));
    self::assertSame($group, $mapper->getFieldset(FormMapper::DEFAULT_FIELDSET)?->childNode('group-group'));
  }

  public function testAddingAFieldRegistersItAndSetsItsMapper(): void
  {
    $mapper = new FormMapper();
    $field = TextLikeField::create('title');
    $mapper->addFieldset('main')->add($field);

    self::assertSame($field, $mapper->getField('title'));
    self::assertTrue($mapper->hasField('title'));
    self::assertSame($mapper, $field->getFormMapper());
    self::assertNull($mapper->getField('unknown'));
  }

  public function testAFieldWithTheSameNameReplacesThePreviousOneAndMoves(): void
  {
    $mapper = new FormMapper();
    $first = TextLikeField::create('title');
    $second = TextLikeField::create('title');
    $main = $mapper->addFieldset('main')->add($first);
    $right = $mapper->addFieldset('right')->add($second);

    self::assertSame($second, $mapper->getField('title'));
    self::assertSame([], $main->getFieldNames());
    self::assertSame(['title'], $right->getFieldNames());
    self::assertNull($first->getFormMapper());
  }

  public function testRemoveDeletesTheFieldFromTheIndexAndTheLayout(): void
  {
    $mapper = new FormMapper();
    $field = TextLikeField::create('start');
    $group = $mapper->addFieldset('main')->addGroup('dates')->add($field);
    $mapper->remove('start')->remove('unknown');

    self::assertFalse($mapper->hasField('start'));
    self::assertSame([], $group->getFieldNames());
    self::assertNull($field->getFormMapper());
  }

  public function testFindNodeSearchesTheWholeLayout(): void
  {
    $mapper = new FormMapper();
    $group = $mapper->addFieldset('main')->addGroup('dates');
    $modal = $mapper->addModal('editor');

    self::assertSame($group, $mapper->findNode('group-dates'));
    self::assertSame($modal, $mapper->findNode('modal-editor'));
    self::assertSame($mapper->getFieldset('main'), $mapper->findNode('fieldset-main'));
    self::assertNull($mapper->findNode('group-unknown'));
  }

  public function testAddModalReturnsTheModalOfTheSameName(): void
  {
    $mapper = new FormMapper();

    self::assertSame($mapper->addModal('editor', 'content'), $mapper->addModal('editor'));
    self::assertCount(1, $mapper->getNodes());
  }

  public function testEndReturnsTheMapper(): void
  {
    $mapper = new FormMapper();

    self::assertSame($mapper, $mapper->end());
  }
}
