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
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Austral Select Field.
 *
 * ChoiceType with the given choices (label => value), or EnumType with the option "enum" (replaces the 3.x
 * EnumField). With select_options.tag, the user can type new values: the field accepts them (choices of the
 * submitted values), and shows the current values of the object, or the values of "tag_choices".
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class SelectField extends AbstractSelectField
{
  /**
   * @param string                   $name
   * @param array<array-key, mixed>  $choices Label => value (option "choices")
   * @param array<string, mixed>     $options
   */
  public function __construct(string $name, array $choices = [], array $options = [])
  {
    parent::__construct($name, ($choices ? ['choices' => $choices] : []) + $options);
  }

  /**
   * @param string                  $name
   * @param array<array-key, mixed> $choices
   * @param array<string, mixed>    $options
   *
   * @return self
   */
  public static function create(string $name, array $choices = [], array $options = []): self
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
      'enum' => null,
      'tag_choices' => null,
    ]);
    $resolver->setAllowedTypes('choices', 'array')
      ->setAllowedTypes('enum', ['null', 'string'])
      ->setAllowedValues('enum', static fn (?string $enum): bool => null === $enum || enum_exists($enum))
      ->setAllowedTypes('tag_choices', ['null', \Closure::class]);
  }

  /**
   * @return class-string
   */
  public function getSymfonyType(): string
  {
    return null !== $this->options['enum'] ? EnumType::class : ChoiceType::class;
  }

  /**
   * @return array<array-key, mixed>
   */
  public function getChoices(): array
  {
    return $this->options['choices'];
  }

  /**
   * @param array<array-key, mixed> $choices
   *
   * @return $this
   */
  public function setChoices(array $choices): static
  {
    return $this->setOptions(['choices' => $choices]);
  }

  /**
   * @return bool
   */
  public function hasTags(): bool
  {
    return $this->options['select_options']['tag'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    if (null !== $this->options['enum'])
    {
      $options += ['class' => $this->options['enum']];
    }
    else
    {
      $options += ['choices' => $this->options['choices']];
    }

    return $options;
  }

  /**
   * With tags, the field is added again with the choices it needs: when the data is set (current values) and
   * when the form is submitted (submitted values), as the 3.x FormType did.
   *
   * {@inheritdoc}
   */
  public function addToForm(FormBuilderInterface $builder, array $options): void
  {
    parent::addToForm($builder, $options);
    if (!$this->hasTags() || null !== $this->options['enum'])
    {
      return;
    }
    $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
      if (\is_object($data = $event->getData()) && ($values = $this->currentValues($data)))
      {
        $this->addWithChoices($event->getForm(), $options, $values);
      }
    });
    $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options): void {
      $submitted = $event->getData();
      $values = \is_array($submitted) ? ($submitted[$this->name] ?? null) : null;
      if (null !== $values && '' !== $values && [] !== $values)
      {
        $this->addWithChoices($event->getForm(), $options, (array) $values);
      }
    });
  }

  /**
   * Values to show for the object: "tag_choices" if given (closure receiving the object, returning
   * label => value), otherwise the current value of the property.
   *
   * @param object $data
   *
   * @return array<array-key, mixed>
   */
  private function currentValues(object $data): array
  {
    if ($this->options['tag_choices'])
    {
      return (array) ($this->options['tag_choices'])($data);
    }
    $accessor = PropertyAccess::createPropertyAccessor();
    if (!$this->options['mapped'] || !$accessor->isReadable($data, $this->name))
    {
      return [];
    }
    $value = $accessor->getValue($data, $this->name);

    return null === $value || '' === $value ? [] : array_values((array) $value);
  }

  /**
   * @param FormInterface<mixed>    $form
   * @param array<string, mixed>    $options
   * @param array<array-key, mixed> $values
   *
   * @return void
   */
  private function addWithChoices(FormInterface $form, array $options, array $values): void
  {
    $choices = $options['choices'];
    foreach ($values as $key => $value)
    {
      if (is_scalar($value) && !\in_array($value, $choices, true))
      {
        $choices[\is_string($key) ? $key : (string) $value] = $value;
      }
    }
    $form->add($this->name, ChoiceType::class, ['choices' => $choices] + $options);
  }
}
