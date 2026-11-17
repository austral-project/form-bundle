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

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Form Mappers.
 * @template T of object
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class FormMappers
{

  /**
   * @var array<string, FormMapper<T>>
   */
  protected array $formMappers = [];

  /**
   * Mapper constructor.
   *
   */
  public function __construct(#[Autowire(service: 'event_dispatcher')] protected EventDispatcherInterface $dispatcher)
  {
  }

  /**
   * createFormMapper
   *
   * @param string $formMapperName
   * @return FormMapper<T>
   */
  public function createFormMapper(string $formMapperName): FormMapper
  {
    /** @var FormMapper<T> $formMapper */
    $formMapper = new FormMapper($this->dispatcher, $formMapperName);
    $this->formMappers[$formMapperName] = $formMapper;
    return $formMapper;
  }

  /**
   * getFormMapper
   *
   * @param string $formMapperName
   * @return FormMapper<T>|null
   */
  public function getFormMapper(string $formMapperName): ?FormMapper
  {
    return array_key_exists($formMapperName, $this->formMappers) ? $this->formMappers[$formMapperName] : null;
  }

  /**
   * addFormMapper
   *
   * @param string $formMapperName
   * @param FormMapper<T> $formMapper
   * @return $this
   */
  public function addFormMapper(string $formMapperName, FormMapper $formMapper): static
  {
    $this->formMappers[$formMapperName] = $formMapper;
    return $this;
  }

  /**
   * getFormMappers
   *
   * @return array<string, FormMapper<T>>
   */
  public function getFormMappers(): array
  {
    return $this->formMappers;
  }


}