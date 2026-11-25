<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Layout;

use Austral\FormBundle\Layout\Fieldset;
use Austral\FormBundle\Layout\Group;
use Austral\FormBundle\Layout\LayoutNode;
use Austral\FormBundle\Layout\Modal;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\TextLikeField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

final class LayoutTest extends TestCase
{
  public function testIdsArePrefixedByTheNodeType(): void
  {
    $mapper = new FormMapper();
    $fieldset = $mapper->addFieldset('fieldset.generalInformation');

    self::assertSame('fieldset-fieldset-generalinformation', $fieldset->getId());
    self::assertSame('group-dates', $fieldset->addGroup('Dates')->getId());
    self::assertSame('modal-link-editor', $fieldset->addModal('link editor')->getId());
  }

  public function testChildrenKeepTheInsertionOrder(): void
  {
    $fieldset = new FormMapper()->addFieldset('main');
    $fieldset->add(TextLikeField::create('a'));
    $group = $fieldset->addGroup('group');
    $fieldset->add(TextLikeField::create('b'));

    self::assertSame(['a', $group, 'b'], $fieldset->getChildren());
    self::assertSame(['a', 'b'], $fieldset->getFieldNames());
    self::assertSame([$group], $fieldset->getChildNodes());
  }

  public function testAnExplicitOrderCompetesWithTheInsertionIndex(): void
  {
    $fieldset = new FormMapper()->addFieldset('main');
    $fieldset->add(TextLikeField::create('a'))
      ->add(TextLikeField::create('b'))
      ->add(TextLikeField::create('first'), -1)
      ->add(TextLikeField::create('last'), 1000);

    self::assertSame(['first', 'a', 'b', 'last'], $fieldset->getFieldNames());
  }

  public function testTheOrderOfANodeCanBeChangedAfterItsCreation(): void
  {
    $fieldset = new FormMapper()->addFieldset('main');
    $fieldset->add(TextLikeField::create('a'));
    $group = $fieldset->addGroup('group');
    $group->setOrder(-1);

    self::assertSame([$group, 'a'], $fieldset->getChildren());
  }

  public function testAddGroupAndAddModalReturnTheExistingNodeOfTheSameName(): void
  {
    $fieldset = new FormMapper()->addFieldset('main');

    self::assertSame($fieldset->addGroup('dates'), $fieldset->addGroup('dates'));
    self::assertSame($fieldset->addModal('editor'), $fieldset->addModal('editor'));
    self::assertCount(2, $fieldset->getChildNodes());
  }

  public function testEndReturnsTheParentThenTheMapper(): void
  {
    $mapper = new FormMapper();
    $fieldset = $mapper->addFieldset('main');
    $group = $fieldset->addGroup('dates');

    self::assertSame($fieldset, $group->end());
    self::assertSame($mapper, $fieldset->end());
    self::assertSame($fieldset, $group->getParent());
    self::assertNull($fieldset->getParent());
    self::assertSame($mapper, $group->getFormMapper());
  }

  public function testRemoveFieldSearchesTheDescendants(): void
  {
    $fieldset = new FormMapper()->addFieldset('main');
    $group = $fieldset->addGroup('dates')->add(TextLikeField::create('start'));

    self::assertTrue($fieldset->removeField('start'));
    self::assertSame([], $group->getFieldNames());
    self::assertFalse($fieldset->removeField('start'));
  }

  public function testFindNodeSearchesTheDescendants(): void
  {
    $fieldset = new FormMapper()->addFieldset('main');
    $modal = $fieldset->addGroup('dates')->addModal('picker');

    self::assertSame($modal, $fieldset->findNode('modal-picker'));
    self::assertNull($fieldset->findNode('modal-unknown'));
  }

  public function testVisibilityAcceptsABooleanOrAClosure(): void
  {
    $fieldset = new FormMapper()->addFieldset('main');

    self::assertTrue($fieldset->isVisible());
    self::assertFalse($fieldset->setVisible(false)->isVisible());
    self::assertTrue($fieldset->setVisible(static fn (LayoutNode $node): bool => 'main' === $node->getName())->isVisible());
  }

  public function testFieldsetDefaults(): void
  {
    $fieldset = new FormMapper()->addFieldset('fieldset.seo');

    self::assertSame('fieldset.seo', $fieldset->getLabel());
    self::assertSame(Fieldset::POSITION_MASTER, $fieldset->getPosition());
    self::assertFalse($fieldset->isCollapse());
    self::assertTrue($fieldset->isShowLabel());
    self::assertSame([], $fieldset->getAttr());
  }

  public function testFieldsetSetters(): void
  {
    $fieldset = new FormMapper()->addFieldset('seo', false, Fieldset::POSITION_RIGHT)
      ->setCollapse()
      ->setShowLabel(false)
      ->setAttr(['class' => 'fieldset-seo']);

    self::assertFalse($fieldset->getLabel());
    self::assertSame(Fieldset::POSITION_RIGHT, $fieldset->getPosition());
    self::assertSame(Fieldset::POSITION_NONE, $fieldset->setPosition(Fieldset::POSITION_NONE)->getPosition());
    self::assertTrue($fieldset->isCollapse());
    self::assertFalse($fieldset->isShowLabel());
    self::assertSame(['class' => 'fieldset-seo'], $fieldset->getAttr());
  }

  public function testLabelParametersAreResolvedWithTheDataOfTheForm(): void
  {
    $fieldset = new FormMapper()->addFieldset('fieldset.content_by_domain')
      ->setLabelParameters(['%domainName%' => '', '%static%' => 'kept'])
      ->setLabelParametersResolver(static fn (Fieldset $fieldset, ?object $data): array => ['%domainName%' => $data->name ?? 'none']);

    self::assertSame(['%domainName%' => 'example.com', '%static%' => 'kept'], $fieldset->resolveLabelParameters((object) ['name' => 'example.com']));
    self::assertSame(['%domainName%' => 'none', '%static%' => 'kept'], $fieldset->resolveLabelParameters());
  }

  public function testGroupDefaultsAndSetters(): void
  {
    $group = new FormMapper()->addFieldset('main')->addGroup('dates');

    self::assertNull($group->getLabel());
    self::assertSame(Group::DIRECTION_ROW, $group->getDirection());
    self::assertSame(Group::SIZE_AUTO, $group->getSize());
    self::assertNull($group->getStyle());

    $group->setDirection(Group::DIRECTION_COLUMN)->setSize(Group::SIZE_COL_6)->setStyle(Group::STYLE_WHITE);
    self::assertSame('group-column', $group->getDirection());
    self::assertSame('col--6', $group->getSize());
    self::assertSame('white', $group->getStyle());
    self::assertSame(Group::SIZE_AUTO, $group->setSize(null)->getSize());
  }

  public function testModalOptions(): void
  {
    $modal = new FormMapper()->addFieldset('main')->addModal('linkEditor', 'link', [
      'label' => 'modal.link',
      'modal' => ['class' => 'modal-large'],
      'button' => ['label' => 'button.edit'],
    ]);

    self::assertSame('link', $modal->getFieldname());
    self::assertSame('modal.link', $modal->getLabel());
    self::assertSame(['class' => 'modal-large', 'template' => null], $modal->getModalOptions());
    self::assertSame(['label' => 'button.edit', 'picto' => 'austral-picto-edit', 'class' => 'button-picto', 'data' => []], $modal->getButton());
  }

  public function testModalRejectsAnUnknownOption(): void
  {
    $this->expectException(UndefinedOptionsException::class);
    new FormMapper()->addModal('editor', null, ['popin' => []]);
  }

  public function testModalIsALayoutNode(): void
  {
    $modal = new FormMapper()->addModal('editor')->add(TextLikeField::create('content'));

    self::assertInstanceOf(Modal::class, $modal);
    self::assertSame(['content'], $modal->getFieldNames());
  }
}
