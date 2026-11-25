<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Form\Type;

use Austral\FormBundle\Mapper\FormMapper;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral FormType Interface.
 * @template T of object
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface FormTypeInterface
{

  /**
   * @param FormMapper<T> $formMapper
   *
   * @return $this
   */
  public function setFormMapper(FormMapper $formMapper): FormTypeInterface;

  /**
   * @param string $class
   *
   * @return $this
   */
  public function setClass(string $class): FormTypeInterface;

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver): void;

  /**
   * @param FormBuilderInterface $builder
   * @param array<string, mixed> $options
   *
   * @return void
   */
  public function buildForm(FormBuilderInterface $builder, array $options): void;

}
