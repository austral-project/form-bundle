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

use Austral\FormBundle\Field\Field;
use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Austral Field Build Event: field.build.
 *
 * The field can still be changed (options, constraints): it is added to the form after the event.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class FieldBuildEvent extends Event
{
  /**
   * @param FormMapper                  $mapper
   * @param Field                       $field
   * @param FormBuilderInterface<mixed> $builder Builder of the form the field is added to
   */
  public function __construct(
    private readonly FormMapper $mapper,
    private readonly Field $field,
    private readonly FormBuilderInterface $builder,
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
   * @return Field
   */
  public function getField(): Field
  {
    return $this->field;
  }

  /**
   * @return FormBuilderInterface<mixed>
   */
  public function getBuilder(): FormBuilderInterface
  {
    return $this->builder;
  }
}
