<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Handler;

use Austral\FormBundle\Event\FormEvents;
use Austral\FormBundle\Event\FormProcessEvent;
use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Austral Form Handler.
 *
 * Handles the request of an Austral form and saves it when valid:
 *   handleRequest(), submit, then if valid save_before, $save, save_after.
 * Each event is dispatched for the form, then for each Austral sub-form (entries of the collections, at any
 * depth), with its own mapper and data. An exception thrown by a listener or by $save is not caught.
 *
 *   $result = $formHandler->process($form, $request, fn () => $entityManager->flush());
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class FormHandler
{
  /**
   * @param EventDispatcherInterface $dispatcher
   */
  public function __construct(
    private readonly EventDispatcherInterface $dispatcher,
  ) {
  }

  /**
   * @param FormInterface<mixed>                  $form    A form of type AustralFormType
   * @param Request                               $request
   * @param callable(FormInterface<mixed>): mixed $save    Saves the data (flush...)
   *
   * @return FormResult
   */
  public function process(FormInterface $form, Request $request, callable $save): FormResult
  {
    if (!self::mapper($form))
    {
      throw new \LogicException(\sprintf('The form "%s" is not an Austral form (option "mapper" of AustralFormType).', $form->getName()));
    }
    $form->handleRequest($request);
    if (!$form->isSubmitted())
    {
      return new FormResult($form, FormResult::STATUS_NOT_SUBMITTED);
    }
    $this->dispatch(FormEvents::SUBMIT, $form, $request);
    if (!$form->isValid())
    {
      return new FormResult($form, FormResult::STATUS_INVALID);
    }
    $this->dispatch(FormEvents::SAVE_BEFORE, $form, $request);
    $save($form);
    $this->dispatch(FormEvents::SAVE_AFTER, $form, $request);

    return new FormResult($form, FormResult::STATUS_SAVED);
  }

  /**
   * The event for $form if it is an Austral form, then for its children, depth first.
   *
   * @param string               $eventName
   * @param FormInterface<mixed> $form
   * @param Request              $request
   *
   * @return void
   */
  private function dispatch(string $eventName, FormInterface $form, Request $request): void
  {
    if ($mapper = self::mapper($form))
    {
      $this->dispatcher->dispatch(new FormProcessEvent($mapper, $form, $request), $eventName);
    }
    foreach ($form as $child)
    {
      $this->dispatch($eventName, $child, $request);
    }
  }

  /**
   * @param FormInterface<mixed> $form
   *
   * @return FormMapper|null
   */
  private static function mapper(FormInterface $form): ?FormMapper
  {
    $mapper = $form->getConfig()->getOption('mapper');

    return $mapper instanceof FormMapper ? $mapper : null;
  }
}
