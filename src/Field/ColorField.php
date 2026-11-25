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

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Color Field (the 3.x ColorPicker).
 *
 * Text input enhanced by the color picker of the admin: "colorpicker_options" is its configuration
 * (camelCase, as the picker names it), given as JSON in data-colorpicker-options.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class ColorField extends Field
{
  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = TextType::class;

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
    $resolver->setOptions('colorpicker_options', static function (OptionsResolver $picker): void {
      $picker->setDefaults([
        'preview' => true,
        'swatches' => [
          '#000', '#444', '#666', '#999', '#ccc', '#eee', '#f3f3f3', '#fff',
          '#f00', '#f90', '#ff0', '#0f0', '#0ff', '#00f', '#90f', '#f0f',
          '#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#cfe2f3', '#d9d2e9', '#ead1dc',
          '#ea9999', '#f9cb9c', '#ffe599', '#b6d7a8', '#a2c4c9', '#9fc5e8', '#b4a7d6', '#d5a6bd',
          '#e06666', '#f6b26b', '#ffd966', '#93c47d', '#76a5af', '#6fa8dc', '#8e7cc3', '#c27ba0',
          '#c00', '#e69138', '#f1c232', '#6aa84f', '#45818e', '#3d85c6', '#674ea7', '#a64d79',
          '#900', '#b45f06', '#bf9000', '#38761d', '#134f5c', '#0b5394', '#351c75', '#741b47',
          '#600', '#783f04', '#7f6000', '#274e13', '#0c343d', '#073763', '#20124d', '#4c1130',
        ],
      ])
        ->setAllowedTypes('preview', 'bool')
        ->setAllowedTypes('swatches', ['null', 'string[]']);

      $picker->setOptions('components', static function (OptionsResolver $components): void {
        $components->setDefaults(['palette' => false, 'opacity' => false, 'hue' => false])
          ->setAllowedTypes('palette', 'bool')
          ->setAllowedTypes('opacity', 'bool')
          ->setAllowedTypes('hue', 'bool');
      });

      $picker->setOptions('interaction', static function (OptionsResolver $interaction): void {
        foreach (['hex', 'rgba', 'hsla', 'hsva', 'cmyk', 'input', 'clear', 'save'] as $key)
        {
          $interaction->setDefault($key, false)->setAllowedTypes($key, 'bool');
        }
      });
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $options['attr']['data-colorpicker'] = true;
    $options['attr']['data-colorpicker-element'] = "#colorPicker-{$this->name}";
    $options['attr']['data-colorpicker-options'] = json_encode(array_filter($this->options['colorpicker_options'], static fn (mixed $value): bool => null !== $value), \JSON_THROW_ON_ERROR);

    return $options;
  }
}
