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
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Austral Form Mapper Event: configure_before and configure_after.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class FormMapperEvent extends Event
{
  /**
   * @param FormMapper      $mapper
   * @param FormMapper|null $parent Mapper of the form that contains this one (collection), null for the root
   */
  public function __construct(
    private readonly FormMapper $mapper,
    private readonly ?FormMapper $parent = null,
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
   * @return FormMapper|null
   */
  public function getParent(): ?FormMapper
  {
    return $this->parent;
  }

  /**
   * @return bool
   */
  public function isRoot(): bool
  {
    return null === $this->parent;
  }

  /**
   * @return object|null
   */
  public function getObject(): ?object
  {
    return $this->mapper->getObject();
  }
}
