<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Event;

use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Austral Form Process Event: submit, save_before and save_after.
 *
 * Dispatched for the form, then for each Austral sub-form (entry of a collection): getForm() is that form,
 * getMapper() its mapper, getData() its data.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class FormProcessEvent extends Event
{
  /**
   * @param FormMapper           $mapper
   * @param FormInterface<mixed> $form
   * @param Request              $request
   */
  public function __construct(
    private readonly FormMapper $mapper,
    private readonly FormInterface $form,
    private readonly Request $request,
  ) {
  }

  /**
   * @return FormMapper
   */
  public function getMapper(): FormMapper
  {
    return $this->mapper;
  }

  /**
   * @return FormInterface<mixed>
   */
  public function getForm(): FormInterface
  {
    return $this->form;
  }

  /**
   * @return Request
   */
  public function getRequest(): Request
  {
    return $this->request;
  }

  /**
   * @return mixed
   */
  public function getData(): mixed
  {
    return $this->form->getData();
  }

  /**
   * @return bool
   */
  public function isRoot(): bool
  {
    return $this->form->isRoot();
  }
}
