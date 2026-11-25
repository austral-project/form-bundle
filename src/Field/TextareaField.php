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

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Textarea Field.
 *
 * The height is a CSS class added to the textarea (same values as the 3.x SIZE_* constants).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class TextareaField extends Field
{
  public const string HEIGHT_AUTO = 'auto';
  public const string HEIGHT_MIDDLE = 'middle';
  public const string HEIGHT_BIG = 'big';

  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = TextareaType::class;

  /**
   * @param string               $name
   * @param string|null          $height  One of the HEIGHT_* constants, HEIGHT_MIDDLE when null
   * @param array<string, mixed> $options
   *
   * @return self
   */
  public static function create(string $name, ?string $height = self::HEIGHT_MIDDLE, array $options = []): self
  {
    return new self($name, ['height' => $height ?? self::HEIGHT_MIDDLE] + $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('height', self::HEIGHT_MIDDLE);
    $resolver->setAllowedValues('height', [self::HEIGHT_AUTO, self::HEIGHT_MIDDLE, self::HEIGHT_BIG]);
  }

  /**
   * @return string
   */
  public function getHeight(): string
  {
    return $this->options['height'];
  }

  /**
   * @param string $height
   *
   * @return $this
   */
  public function setHeight(string $height): static
  {
    return $this->setOptions(['height' => $height]);
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $options['attr']['class'] = trim(($options['attr']['class'] ?? '').' '.$this->options['height']);
    if (self::HEIGHT_AUTO === $this->options['height'])
    {
      $options['attr'] += ['rows' => 2];
    }

    return $options;
  }
}
