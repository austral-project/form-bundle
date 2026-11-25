<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Form;

use Austral\FormBundle\Constraint\DoctrineConstraintGuesser;
use Austral\FormBundle\Event\FieldBuildEvent;
use Austral\FormBundle\Event\FormEvents;
use Austral\FormBundle\Field\CollectionEmbedField;
use Austral\FormBundle\Field\TextField;
use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\Gallery;
use Austral\FormBundle\Tests\Fixtures\GalleryItem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Validation;

final class FieldBuildEventTest extends TestCase
{
  public function testListenerChangesTheFieldBeforeItIsAdded(): void
  {
    $dispatcher = new EventDispatcher();
    $dispatcher->addListener(FormEvents::FIELD_BUILD, static function (FieldBuildEvent $event): void {
      if ('title' === $event->getField()->getName())
      {
        $event->getField()->setOptions(['picto' => 'austral-picto-text'])->addConstraint(new NotBlank());
      }
    });
    $mapper = new FormMapper('form_austral', Gallery::class)->add(TextField::create('title', ['required' => true]));

    $form = $this->factory($dispatcher)->createNamed('form_austral', AustralFormType::class, new Gallery(), ['mapper' => $mapper]);

    self::assertSame('austral-picto-text', $form->createView()['title']->vars['austral']['picto']);
    self::assertSame([NotNull::class, NotBlank::class], array_map(static fn (object $constraint): string => $constraint::class, $form->get('title')->getConfig()->getOption('constraints')));
  }

  public function testDispatchedForTheFieldsOfTheEntries(): void
  {
    $dispatcher = new EventDispatcher();
    $built = [];
    $dispatcher->addListener(FormEvents::FIELD_BUILD, static function (FieldBuildEvent $event) use (&$built): void {
      $built[] = $event->getMapper()->getName().'.'.$event->getField()->getName().'@'.$event->getBuilder()->getName();
    });
    $items = new FormMapper('item', GalleryItem::class)->add(TextField::create('label'));
    $mapper = new FormMapper('form_austral', Gallery::class)
      ->add(TextField::create('title'))
      ->add(CollectionEmbedField::create('items', ['entry' => ['mapper' => $items]]));
    $gallery = new Gallery();
    $gallery->items = [new GalleryItem('A')];

    $this->factory($dispatcher)->createNamed('form_austral', AustralFormType::class, $gallery, ['mapper' => $mapper]);

    self::assertContains('form_austral.title@form_austral', $built);
    self::assertContains('form_austral.items@form_austral', $built);
    self::assertContains('item.label@0', $built);
    self::assertContains('item.label@__name__', $built);
  }

  public function testWithoutDispatcher(): void
  {
    $mapper = new FormMapper('form_austral')->add(TextField::create('title'));

    $form = $this->factory(null)->createNamed('form_austral', AustralFormType::class, null, ['mapper' => $mapper]);

    self::assertTrue($form->has('title'));
  }

  /**
   * @param EventDispatcher|null $dispatcher
   *
   * @return FormFactoryInterface
   */
  private function factory(?EventDispatcher $dispatcher): FormFactoryInterface
  {
    return Forms::createFormFactoryBuilder()
      ->addExtension(new ValidatorExtension(Validation::createValidator()))
      ->addType(new AustralFormType(new DoctrineConstraintGuesser(), $dispatcher))
      ->addTypeExtension(new PresentationTypeExtension())
      ->getFormFactory();
  }
}
