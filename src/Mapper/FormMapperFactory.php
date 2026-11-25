<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Mapper;

use Austral\FormBundle\Event\FormEvents;
use Austral\FormBundle\Event\FormMapperEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Austral Form Mapper Factory.
 *
 * Creates a FormMapper and lets the other bundles complete it: configure_before, the configuration given by
 * the caller (configureFormMapper() of an Admin class...), then configure_after for the mapper and for each
 * mapper of its collections, at any depth.
 *
 *   $mapper = $mapperFactory->create('form_austral', Page::class, $page, $admin->configureFormMapper(...));
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class FormMapperFactory
{
  /**
   * @param EventDispatcherInterface $dispatcher
   */
  public function __construct(private readonly EventDispatcherInterface $dispatcher)
  {
  }

  /**
   * @param string                             $name      Name of the form (prefix of the HTML names)
   * @param class-string|null                  $dataClass Defaults to the class of $data
   * @param object|null                        $data
   * @param (callable(FormMapper): mixed)|null $configure Configuration of the mapper
   *
   * @return FormMapper
   */
  public function create(string $name, ?string $dataClass = null, ?object $data = null, ?callable $configure = null): FormMapper
  {
    $mapper = new FormMapper($name, $dataClass, $data);
    $this->dispatcher->dispatch(new FormMapperEvent($mapper), FormEvents::CONFIGURE_BEFORE);
    if (null !== $configure)
    {
      $configure($mapper);
    }
    $done = [];
    $this->configured($mapper, null, $done);

    return $mapper;
  }

  /**
   * configure_after for $mapper, then for its sub-mappers (a listener can add collections: they are read after it).
   *
   * @param FormMapper       $mapper
   * @param FormMapper|null  $parent
   * @param array<int, true> $done   Ids of the mappers already done: a mapper used by two collections gets the event once
   *
   * @return void
   */
  private function configured(FormMapper $mapper, ?FormMapper $parent, array &$done): void
  {
    if (isset($done[spl_object_id($mapper)]))
    {
      return;
    }
    $done[spl_object_id($mapper)] = true;
    $this->dispatcher->dispatch(new FormMapperEvent($mapper, $parent), FormEvents::CONFIGURE_AFTER);
    foreach ($mapper->getSubMappers() as $subMapper)
    {
      $this->configured($subMapper, $mapper, $done);
    }
  }
}
