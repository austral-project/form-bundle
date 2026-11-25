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

use Austral\FormBundle\Event\FormEvents;
use Austral\FormBundle\Event\FormMapperEvent;
use Austral\FormBundle\Field\CollectionEmbedField;
use Austral\FormBundle\Field\TextField;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Mapper\FormMapperFactory;
use Austral\FormBundle\Tests\Fixtures\Gallery;
use Austral\FormBundle\Tests\Fixtures\GalleryItem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class FormMapperFactoryTest extends TestCase
{
  public function testCreate(): void
  {
    $gallery = new Gallery();

    $mapper = (new FormMapperFactory(new EventDispatcher()))->create('form_austral', null, $gallery);

    self::assertSame('form_austral', $mapper->getName());
    self::assertSame(Gallery::class, $mapper->getDataClass());
    self::assertSame($gallery, $mapper->getObject());
  }

  public function testEventsAroundTheConfiguration(): void
  {
    $dispatcher = new EventDispatcher();
    $calls = [];
    $dispatcher->addListener(FormEvents::CONFIGURE_BEFORE, static function (FormMapperEvent $event) use (&$calls): void {
      $calls[] = 'before:'.$event->getMapper()->getName().':'.\count($event->getMapper()->getFields());
      $event->getMapper()->add(TextField::create('fromBefore'));
    });
    $dispatcher->addListener(FormEvents::CONFIGURE_AFTER, static function (FormMapperEvent $event) use (&$calls): void {
      $calls[] = 'after:'.$event->getMapper()->getName().':'.implode(',', array_keys($event->getMapper()->getFields()));
      self::assertTrue($event->isRoot());
    });

    (new FormMapperFactory($dispatcher))->create('form_austral', Gallery::class, null, static function (FormMapper $mapper) use (&$calls): void {
      $calls[] = 'configure';
      $mapper->add(TextField::create('title'));
    });

    self::assertSame(['before:form_austral:0', 'configure', 'after:form_austral:fromBefore,title'], $calls);
  }

  public function testConfigureAfterForEachSubMapper(): void
  {
    $dispatcher = new EventDispatcher();
    $events = [];
    $dispatcher->addListener(FormEvents::CONFIGURE_AFTER, static function (FormMapperEvent $event) use (&$events): void {
      $events[] = [$event->getMapper()->getName(), $event->getParent()?->getName()];
    });
    $items = new FormMapper('item', GalleryItem::class);
    $tags = new FormMapper('tag');
    $items->add(CollectionEmbedField::create('tags', ['entry' => ['mapper' => $tags]]));
    $variant = new FormMapper('image', GalleryItem::class);

    (new FormMapperFactory($dispatcher))->create('form_austral', Gallery::class, null, static function (FormMapper $mapper) use ($items, $variant): void {
      $mapper->add(CollectionEmbedField::create('items', ['entry' => ['mapper' => $items]]));
      $mapper->add(CollectionEmbedField::create('blocks')->addVariant('image', CollectionEmbedField::create('blocks_image', ['entry' => ['mapper' => $variant]])));
      $mapper->add(CollectionEmbedField::create('sameItems', ['entry' => ['mapper' => $items]]));
    });

    self::assertSame([['form_austral', null], ['item', 'form_austral'], ['tag', 'item'], ['image', 'form_austral']], $events);
  }

  public function testSubMappers(): void
  {
    $items = new FormMapper('item');
    $mapper = (new FormMapper())
      ->add(TextField::create('title'))
      ->add(CollectionEmbedField::create('items', ['entry' => ['mapper' => $items]]))
      ->add(CollectionEmbedField::create('keywords'));

    self::assertSame([$items], $mapper->getSubMappers());
  }
}
