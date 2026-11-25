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

use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Time Field (the 3.x TimeField and ClockPicker).
 *
 * TimeType as a single text input. With "picker" (default), the clock picker of the admin is used:
 * "clockpicker_options" is its configuration (camelCase, as the picker names it), given as JSON in
 * data-clockpicker-options.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class TimeField extends Field
{
  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = TimeType::class;

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
    $resolver->setDefaults([
      'picto' => 'austral-picto-clock',
      'picker' => true,
    ]);
    $resolver->setAllowedTypes('picker', 'bool');
    $resolver->setOptions('clockpicker_options', static function (OptionsResolver $picker): void {
      $picker->setDefaults([
        'clockType' => '24h',
        'switchToMinutesAfterSelectHour' => true,
        'enableScrollbar' => false,
      ])
        ->setAllowedTypes('clockType', 'string')
        ->setAllowedTypes('switchToMinutesAfterSelectHour', 'bool')
        ->setAllowedTypes('enableScrollbar', 'bool');
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $options += ['widget' => 'single_text'];
    if (!$this->options['picker'])
    {
      return $options;
    }
    $options += ['with_seconds' => false, 'html5' => false];
    $options['attr']['class'] = trim(($options['attr']['class'] ?? '').' timepicker-ui-input');
    $options['attr']['data-clockpicker'] = true;
    $options['attr']['data-clockpicker-options'] = json_encode($this->options['clockpicker_options'], \JSON_THROW_ON_ERROR);

    return $options;
  }
}
