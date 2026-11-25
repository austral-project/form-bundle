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

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

use function Symfony\Component\String\u;

/**
 * Austral Password Field.
 *
 * RepeatedType of two PasswordType ("repeat", default), or a single PasswordType. With "auto_constraints",
 * the input is required in HTML and must match "match" (Regex, message errors.<name>.regex), as in 3.x; the
 * field itself is not required, so an empty password can keep the current one.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class PasswordField extends Field
{
  /**
   * At least 8 characters, a lowercase letter, an uppercase letter and a digit.
   */
  public const string MATCH_DEFAULT = '#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$#';

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
      'repeat' => true,
      'type' => PasswordType::class,
      'match' => self::MATCH_DEFAULT,
    ]);
    $resolver->setAllowedTypes('repeat', 'bool')
      ->setAllowedTypes('type', 'string')
      ->setAllowedTypes('match', ['null', 'string']);
  }

  /**
   * @return class-string
   */
  public function getSymfonyType(): string
  {
    return $this->options['repeat'] ? RepeatedType::class : $this->options['type'];
  }

  /**
   * @return string|null
   */
  public function getMatch(): ?string
  {
    return $this->options['match'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAutoConstraints(): array
  {
    return null !== $this->options['match'] ? [new Regex(pattern: $this->options['match'], message: "errors.{$this->name}.regex")] : [];
  }

  /**
   * Translation keys of the 3.x: <label>.master for the repeated field, <label>.master.first / .second for
   * its two inputs, field.error.<name>.repeat when they differ.
   *
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    if ($this->options['auto_constraints'])
    {
      $options['required'] = true;
    }
    $inputAttr = ['autocomplete' => 'new-password', 'data-password' => ''];
    if (!$this->options['repeat'])
    {
      $options['attr'] += $inputAttr;

      return $options;
    }
    $label = $options['label'];
    if (\is_string($label))
    {
      $options['label'] = $label = "{$label}.master";
    }
    $options += [
      'type' => $this->options['type'],
      'first_options' => ['label' => \is_string($label) ? "{$label}.first" : $label, 'attr' => $inputAttr],
      'second_options' => ['label' => \is_string($label) ? "{$label}.second" : $label, 'attr' => $inputAttr],
      'invalid_message' => "field.error.{$this->name}.repeat",
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getWidget(): array
  {
    return [
      'repeat' => $this->options['repeat'],
      'type_class' => u(new \ReflectionClass($this->options['type'])->getShortName())->camel()->toString(),
    ];
  }
}
