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

use Austral\FormBundle\Field\Base\FieldInterface;
use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Austral Form Field Event.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class FormFieldEvent extends Event
{

  const EVENT_AUSTRAL_FIELD_CONFIGURATION = "austral.event.form.field.configuration";
  const EVENT_AUSTRAL_FORM_PRE_SET_DATA = "austral.event.form.field.pre_set_data";

  /**
   * @var FormMapper
   */
  private FormMapper $formMapper;

  /**
   * @var FieldInterface
   */
  private FieldInterface $field;

  /**
   * @var mixed
   */
  private $data;

  /**
   * @var bool
   */
  private bool $updatePreSetData = false;

  /**
   * FormFieldEvent constructor.
   *
   * @param FormMapper $formMapper
   * @param FieldInterface $field
   * @param null $data
   */
  public function __construct(FormMapper $formMapper, FieldInterface $field, $data = null)
  {
    $this->formMapper = $formMapper;
    $this->field = $field;
    $this->data = $data;
  }

  /**
   * @return FormMapper
   */
  public function getFormMapper(): FormMapper
  {
    return $this->formMapper;
  }

  /**
   * @return FieldInterface
   */
  public function getField(): FieldInterface
  {
    return $this->field;
  }

  /**
   * @param FieldInterface $field
   *
   * @return FormFieldEvent
   */
  public function setField(FieldInterface $field): FormFieldEvent
  {
    $this->field = $field;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * @param mixed $data
   *
   * @return $this
   */
  public function setData($data): FormFieldEvent
  {
    $this->data = $data;
    return $this;
  }

  /**
   * @return bool
   */
  public function getUpdatePreSetData(): bool
  {
    return $this->updatePreSetData;
  }

  /**
   * @param bool $updatePreSetData
   *
   * @return $this
   */
  public function setUpdatePreSetData(bool $updatePreSetData): FormFieldEvent
  {
    $this->updatePreSetData = $updatePreSetData;
    return $this;
  }

}