<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Handler;

use Austral\FormBundle\Constraint\DoctrineConstraintGuesser;
use Austral\FormBundle\Event\FormEvents;
use Austral\FormBundle\Event\FormProcessEvent;
use Austral\FormBundle\Field\CollectionEmbedField;
use Austral\FormBundle\Field\TextField;
use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Handler\FormHandler;
use Austral\FormBundle\Handler\FormResult;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\Gallery;
use Austral\FormBundle\Tests\Fixtures\GalleryItem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

final class FormHandlerTest extends TestCase
{
  private EventDispatcher $dispatcher;

  /**
   * @var list<string>
   */
  private array $calls = [];

  protected function setUp(): void
  {
    $this->dispatcher = new EventDispatcher();
    $this->calls = [];
    foreach ([FormEvents::SUBMIT, FormEvents::SAVE_BEFORE, FormEvents::SAVE_AFTER] as $eventName)
    {
      $this->dispatcher->addListener($eventName, function (FormProcessEvent $event, string $eventName): void {
        $this->calls[] = substr($eventName, \strlen('austral.form.')).':'.$event->getMapper()->getName().':'.$event->getForm()->getName();
      });
    }
  }

  public function testNotSubmitted(): void
  {
    $result = $this->handler()->process($this->form(new Gallery()), Request::create('/'), function (): void {
      $this->calls[] = 'save';
    });

    self::assertSame(FormResult::STATUS_NOT_SUBMITTED, $result->getStatus());
    self::assertFalse($result->isSubmitted());
    self::assertFalse($result->isSaved());
    self::assertSame([], $this->calls);
  }

  public function testInvalid(): void
  {
    $result = $this->handler()->process($this->form(new Gallery()), $this->post(['title' => '']), function (): void {
      $this->calls[] = 'save';
    });

    self::assertSame(FormResult::STATUS_INVALID, $result->getStatus());
    self::assertTrue($result->isSubmitted());
    self::assertSame(['submit:form_austral:form_austral'], $this->calls);
  }

  public function testSaved(): void
  {
    $gallery = new Gallery();
    $form = $this->form($gallery);

    $result = $this->handler()->process($form, $this->post(['title' => 'Summer']), function (FormInterface $saved) use ($form, $gallery): void {
      self::assertSame($form, $saved);
      self::assertSame('Summer', $gallery->title);
      $this->calls[] = 'save';
    });

    self::assertSame(FormResult::STATUS_SAVED, $result->getStatus());
    self::assertTrue($result->isSaved());
    self::assertSame($form, $result->getForm());
    self::assertSame(['submit:form_austral:form_austral', 'save_before:form_austral:form_austral', 'save', 'save_after:form_austral:form_austral'], $this->calls);
  }

  public function testEventsForEachEntryOfTheCollections(): void
  {
    $gallery = new Gallery();
    $gallery->items = [new GalleryItem('A'), new GalleryItem('B')];
    $data = [];
    $this->dispatcher->addListener(FormEvents::SAVE_BEFORE, static function (FormProcessEvent $event) use (&$data): void {
      $data[] = $event->isRoot() ? 'root' : ($event->getData() instanceof GalleryItem ? $event->getData()->label : null);
    });

    $this->handler()->process($this->form($gallery), $this->post(['title' => 'Summer', 'items' => [['label' => 'A'], ['label' => 'B2']]]), static function (): void {
    });

    self::assertSame([
      'submit:form_austral:form_austral', 'submit:item:0', 'submit:item:1',
      'save_before:form_austral:form_austral', 'save_before:item:0', 'save_before:item:1',
      'save_after:form_austral:form_austral', 'save_after:item:0', 'save_after:item:1',
    ], $this->calls);
    self::assertSame(['root', 'A', 'B2'], $data);
  }

  public function testSubmitListenerCanInvalidateTheForm(): void
  {
    $this->dispatcher->addListener(FormEvents::SUBMIT, static function (FormProcessEvent $event): void {
      $event->getForm()->addError(new FormError('upload.error'));
    });

    $result = $this->handler()->process($this->form(new Gallery()), $this->post(['title' => 'Summer']), static function (): void {
    });

    self::assertSame(FormResult::STATUS_INVALID, $result->getStatus());
  }

  public function testExceptionOfAListenerIsNotCaught(): void
  {
    $this->dispatcher->addListener(FormEvents::SAVE_BEFORE, static function (): void {
      throw new \RuntimeException('Cannot move the file');
    });

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Cannot move the file');

    $this->handler()->process($this->form(new Gallery()), $this->post(['title' => 'Summer']), static function (): void {
    });
  }

  public function testOnlyAustralForms(): void
  {
    $form = Forms::createFormFactory()->create(FormType::class);

    $this->expectException(\LogicException::class);

    $this->handler()->process($form, Request::create('/'), static function (): void {
    });
  }

  /**
   * @return FormHandler
   */
  private function handler(): FormHandler
  {
    return new FormHandler($this->dispatcher);
  }

  /**
   * @param array<string, mixed> $data
   *
   * @return Request
   */
  private function post(array $data): Request
  {
    return Request::create('/', 'POST', ['form_austral' => $data]);
  }

  /**
   * @param Gallery $gallery
   *
   * @return FormInterface<mixed>
   */
  private function form(Gallery $gallery): FormInterface
  {
    $items = (new FormMapper('item', GalleryItem::class))->add(TextField::create('label'));
    $mapper = (new FormMapper('form_austral', Gallery::class))
      ->add(TextField::create('title', ['required' => true]))
      ->add(CollectionEmbedField::create('items', ['entry' => ['mapper' => $items]]));

    return Forms::createFormFactoryBuilder()
      ->addExtension(new HttpFoundationExtension())
      ->addExtension(new ValidatorExtension(Validation::createValidator()))
      ->addType(new AustralFormType(new DoctrineConstraintGuesser(), $this->dispatcher))
      ->addTypeExtension(new PresentationTypeExtension())
      ->getFormFactory()
      ->createNamed($mapper->getName(), AustralFormType::class, $gallery, ['mapper' => $mapper]);
  }
}
