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
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Austral Form Event.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class FormEvent extends Event
{

  const EVENT_AUSTRAL_FORM_VALIDATE = "austral.event.form.validate";
  const EVENT_AUSTRAL_FORM_UPDATE_BEFORE = "austral.event.form.update_before";
  const EVENT_AUSTRAL_FORM_UPDATE_AFTER = "austral.event.form.update_after";
  const EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_BEFORE = "austral.event.form.add_auto_fields_before";
  const EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER = "austral.event.form.add_auto_fields_after";
  const EVENT_AUSTRAL_FORM_INIT_END = "austral.event.form.init_end";

  /**
   * @var FormInterface|null
   */
  private ?FormInterface $form;

  /**
   * @var FormMapper
   */
  private FormMapper $formMapper;

  /**
   * FormEvent constructor.
   *
   * @param FormMapper $formMapper
   * @param ?FormInterface $form
   */
  public function __construct(FormMapper $formMapper, FormInterface $form = null)
  {
    $this->form = $form;
    $this->formMapper = $formMapper;
  }

  /**
   * @return FormInterface|null
   */
  public function getForm(): ?FormInterface
  {
    return $this->form;
  }

  /**
   * @var FormInterface $form
   * @return $this
   */
  public function setForm(FormInterface $form): FormEvent
  {
    $this->form = $form;
    return $this;
  }

  /**
   * @return FormMapper
   */
  public function getFormMapper(): FormMapper
  {
    return $this->formMapper;
  }

  /**
   * Get status
   * @return string|null
   */
  public function getStatus(): ?string
  {
    return $this->formMapper->getFormStatus();
  }

  /**
   * @param string|null $status
   *
   * @return FormEvent
   */
  public function setStatus(?string $status): FormEvent
  {
    $this->formMapper->setFormStatus($status);
    return $this;
  }

}