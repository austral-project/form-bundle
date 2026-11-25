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

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Switch Field.
 *
 * A checkbox shown as a switch (or as a checkbox: "appearance", replaces the 3.x CheckboxField). Its title,
 * summary, pictos and colors are exposed as form.vars.austral.widget; the colors as CSS variables in "style".
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class SwitchField extends Field
{
  public const string APPEARANCE_SWITCH = 'switch';
  public const string APPEARANCE_CHECKBOX = 'checkbox';

  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = CheckboxType::class;

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
      'appearance' => self::APPEARANCE_SWITCH,
      'title' => null,
      'summary' => null,
    ]);
    $resolver->setAllowedValues('appearance', [self::APPEARANCE_SWITCH, self::APPEARANCE_CHECKBOX])
      ->setAllowedTypes('title', ['null', 'string'])
      ->setAllowedTypes('summary', ['null', 'string']);
    $resolver->setOptions('pictos', static function (OptionsResolver $pictos): void {
      $pictos->setDefaults(['enabled' => null, 'disabled' => null])
        ->setAllowedTypes('enabled', ['null', 'string'])
        ->setAllowedTypes('disabled', ['null', 'string']);
    });

    $resolver->setOptions('button_colors', static function (OptionsResolver $colors): void {
      $colors->setDefaults(['enabled' => 'var(--color-white-force)', 'disabled' => 'var(--color-white-force)'])
        ->setAllowedTypes('enabled', ['null', 'string'])
        ->setAllowedTypes('disabled', ['null', 'string']);
    });
    $resolver->setOptions('background_colors', static function (OptionsResolver $colors): void {
      $colors->setDefaults(['enabled' => 'var(--color-green-40)', 'disabled' => 'var(--color-main-40)'])
        ->setAllowedTypes('enabled', ['null', 'string'])
        ->setAllowedTypes('disabled', ['null', 'string']);
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $options['attr'] += ['autocomplete' => 'off'];

    return $options;
  }

  /**
   * CSS variables of the colors, as in 3.x (the admin theme reads --switch-button-color-* and --switch-fond-color-*).
   *
   * @return string
   */
  public function getStyle(): string
  {
    $styles = [];
    foreach (['button_colors' => 'button', 'background_colors' => 'fond'] as $option => $variable)
    {
      foreach ($this->options[$option] as $state => $color)
      {
        if (null !== $color)
        {
          $styles[] = "--switch-{$variable}-color-{$state}:{$color};";
        }
      }
    }

    return implode(' ', $styles);
  }

  /**
   * {@inheritdoc}
   */
  protected function getWidget(): array
  {
    return [
      'appearance' => $this->options['appearance'],
      'title' => $this->options['title'],
      'summary' => $this->options['summary'],
      'pictos' => $this->options['pictos'],
      'style' => $this->getStyle(),
    ];
  }
}
