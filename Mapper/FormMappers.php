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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Form Mappers.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class FormMappers
{

  /**
   * @var EventDispatcherInterface|null
   */
  protected ?EventDispatcherInterface $dispatcher;

  /**
   * @var array
   */
  protected array $formMappers = array();

  /**
   * Mapper constructor.
   *
   */
  public function __construct(EventDispatcherInterface $dispatcher = null)
  {
    $this->dispatcher = $dispatcher;
  }

  /**
   * createFormMapper
   *
   * @param $formMapperName
   * @return FormMapper
   */
  public function createFormMapper($formMapperName): FormMapper
  {
    $this->formMappers[$formMapperName] = (new FormMapper($this->dispatcher))->setName($formMapperName);
    return $this->formMappers[$formMapperName];
  }

  /**
   * getFormMapper
   *
   * @param string $formMapperName
   * @return FormMapper|null
   */
  public function getFormMapper(string $formMapperName): ?FormMapper
  {
    return array_key_exists($formMapperName, $this->formMappers) ? $this->formMappers[$formMapperName] : null;
  }

  /**
   * addFormMapper
   *
   * @param string $formMapperName
   * @param FormMapper $formMapper
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
   * @return array
   */
  public function getFormMappers(): array
  {
    return $this->formMappers;
  }


}