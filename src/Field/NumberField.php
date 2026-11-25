<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Field;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Number Field.
 *
 * NumberType, or IntegerType with the option "integer" (replaces the 3.x IntegerField). The options of the
 * Symfony type (scale, rounding_mode, html5...) go in "form_options".
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class NumberField extends Field
{
  /**
   * @param string               $name
   * @param array<string, mixed> $options
   *
   * @return self
   */
  public static function create(string $name, array $options = []): self
  {
    return new self($name, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('integer', false);
    $resolver->setAllowedTypes('integer', 'bool');
  }

  /**
   * @return class-string
   */
  public function getSymfonyType(): string
  {
    return $this->options['integer'] ? IntegerType::class : NumberType::class;
  }

  /**
   * @return bool
   */
  public function isInteger(): bool
  {
    return $this->options['integer'];
  }
}
