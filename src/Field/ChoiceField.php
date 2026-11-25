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

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\String\u;

/**
 * Austral Choice Field.
 *
 * Radios (checkboxes with "multiple"), each choice with its own CSS styles. A choice is a value, or
 * ['value' => mixed, 'styles' => list<string>]; without styles, "choices_styles" applies, otherwise the
 * green (true) or main (any other value) styles. The styles are exposed as form.vars.austral.widget.styles,
 * by label.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class ChoiceField extends Field
{
  public const string DIRECTION_HORIZONTAL = 'horizontal';
  public const string DIRECTION_VERTICAL = 'vertical';

  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = ChoiceType::class;

  /**
   * @param string                  $name
   * @param array<array-key, mixed> $choices Label => value, or label => ['value' => mixed, 'styles' => list<string>]
   * @param array<string, mixed>    $options
   */
  public function __construct(string $name, array $choices, array $options = [])
  {
    parent::__construct($name, ['choices' => $choices] + $options);
  }

  /**
   * @param string                  $name
   * @param array<array-key, mixed> $choices
   * @param array<string, mixed>    $options
   *
   * @return self
   */
  public static function create(string $name, array $choices, array $options = []): self
  {
    return new self($name, $choices, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults([
      'choices' => [],
      'multiple' => false,
      'direction' => self::DIRECTION_HORIZONTAL,
      'choice_style' => 'full',
      'choices_styles' => [],
    ]);

    $resolver->setAllowedTypes('choices', 'array')
      ->setAllowedTypes('multiple', 'bool')
      ->setAllowedValues('direction', [self::DIRECTION_HORIZONTAL, self::DIRECTION_VERTICAL])
      ->setAllowedTypes('choice_style', ['null', 'string'])
      ->setAllowedTypes('choices_styles', 'string[]');

    $resolver->setNormalizer('choices', static function ($options, array $choices): array {
      $normalized = [];
      foreach ($choices as $label => $choice)
      {
        if (!\is_array($choice))
        {
          $choice = ['value' => $choice];
        }
        if ($unknown = array_diff(array_keys($choice), ['value', 'styles']))
        {
          throw new \InvalidArgumentException(\sprintf('Unknown keys "%s" in the choice "%s".', implode('", "', $unknown), $label));
        }
        $normalized[$label] = ['value' => $choice['value'] ?? null, 'styles' => $choice['styles'] ?? []];
      }

      return $normalized;
    });
  }

  /**
   * @return array<array-key, mixed> Label => value
   */
  public function getChoices(): array
  {
    return array_map(static fn (array $choice): mixed => $choice['value'], $this->options['choices']);
  }

  /**
   * @return array<array-key, list<string>> Label => CSS styles
   */
  public function getStylesByChoices(): array
  {
    $styles = [];
    foreach ($this->options['choices'] as $label => $choice)
    {
      $styles[$label] = $choice['styles'] ?: ($this->options['choices_styles'] ?: self::defaultStyles($choice['value']));
    }

    return $styles;
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $autocomplete = $options['attr']['autocomplete'] ?? 'off';
    $options += [
      'choices' => $this->getChoices(),
      'multiple' => $this->options['multiple'],
      'expanded' => true,
      'placeholder' => false,
      'choice_attr' => static fn (mixed $value, string|int $label): array => [
        'class' => 'field_'.u(str_replace('.', ' ', strtolower((string) $label)))->snake()->toString(),
        'autocomplete' => $autocomplete,
      ],
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getWidget(): array
  {
    return [
      'multiple' => $this->options['multiple'],
      'direction' => $this->options['direction'],
      'choice_style' => $this->options['choice_style'],
      'styles' => $this->getStylesByChoices(),
    ];
  }

  /**
   * @param mixed $value
   *
   * @return list<string>
   */
  private static function defaultStyles(mixed $value): array
  {
    $color = true === $value ? 'green' : 'main';

    return [
      "--element-choice-current-background:var(--color-{$color}-20)",
      "--element-choice-current-color:var(--color-{$color}-100)",
      "--element-choice-hover-color:var(--color-{$color}-100)",
    ];
  }
}
