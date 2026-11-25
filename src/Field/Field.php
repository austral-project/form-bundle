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

use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotNull;

use function Symfony\Component\String\u;

/**
 * Austral Field.
 *
 * Base of every Austral field: a Symfony form type, its options, constraints and presentation.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
abstract class Field
{
  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = null;

  /**
   * @var bool
   */
  protected bool $displayOnly = false;

  /**
   * @var array<string, mixed>
   */
  private array $rawOptions;

  /**
   * @var array<string, mixed>
   */
  protected array $options;

  /**
   * @var list<Constraint>
   */
  protected array $constraints = [];

  /**
   * @var FormMapper|null
   */
  protected ?FormMapper $formMapper = null;

  /**
   * @param string               $name
   * @param array<string, mixed> $options
   */
  public function __construct(protected string $name, array $options = [])
  {
    $this->rawOptions = $options;
    $this->options = $this->resolveOptions($options);
    $this->setConstraints($this->options['constraints']);
  }

  /**
   * @param OptionsResolver $resolver
   *
   * @return void
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'label' => "fields.{$this->name}.entitled",
      'required' => false,
      'attr' => [],
      'constraints' => [],
      'mapped' => true,
      'getter' => null,
      'setter' => null,
      'placeholder' => null,
      'picto' => null,
      'helper' => null,
      'size' => null,
      'class' => null,
      'container_class' => null,
      'label_position' => 'external',
      'before' => null,
      'after' => null,
      'template' => null,
      'template_vars' => [],
      'visible' => true,
      'auto_constraints' => true,
      'form_options' => [],
    ]);
    $resolver->setAllowedTypes('label', ['string', 'bool', 'null'])
      ->setAllowedValues('label', static fn (string|bool|null $value): bool => true !== $value)
      ->setAllowedTypes('required', 'bool')
      ->setAllowedTypes('attr', 'array')
      ->setAllowedTypes('constraints', Constraint::class.'[]')
      ->setAllowedTypes('mapped', 'bool')
      ->setAllowedTypes('getter', ['null', 'callable'])
      ->setAllowedTypes('setter', ['null', 'callable'])
      ->setAllowedTypes('placeholder', ['null', 'string'])
      ->setAllowedTypes('picto', ['null', 'string'])
      ->setAllowedTypes('helper', ['null', 'string'])
      ->setAllowedTypes('size', ['null', 'string'])
      ->setAllowedTypes('class', ['null', 'string'])
      ->setAllowedTypes('container_class', ['null', 'string'])
      ->setAllowedTypes('label_position', 'string')
      ->setAllowedTypes('before', ['null', 'string', 'array'])
      ->setAllowedTypes('after', ['null', 'string', 'array'])
      ->setAllowedTypes('template', ['null', 'string'])
      ->setAllowedTypes('template_vars', 'array')
      ->setAllowedTypes('visible', ['bool', 'callable', 'array'])
      ->setAllowedTypes('auto_constraints', 'bool')
      ->setAllowedTypes('form_options', 'array');

    $resolver->setNormalizer('before', static fn ($options, mixed $value): ?array => self::normalizeSlot($value));
    $resolver->setNormalizer('after', static fn ($options, mixed $value): ?array => self::normalizeSlot($value));
  }

  /**
   * A slot (before / after the widget) is null or ['html' => ?string, 'template' => ?string, 'component' => ?string, 'parameters' => array].
   *
   * @param string|array<string, mixed>|null $value
   *
   * @return array{html: ?string, template: ?string, component: ?string, parameters: array<string, mixed>}|null
   */
  private static function normalizeSlot(string|array|null $value): ?array
  {
    if (null === $value)
    {
      return null;
    }
    if (\is_string($value))
    {
      $value = ['html' => $value];
    }
    $unknown = array_diff(array_keys($value), ['html', 'template', 'component', 'parameters']);
    if ($unknown)
    {
      throw new \InvalidArgumentException(\sprintf('Unknown slot keys "%s": use html, template, component or parameters.', implode('", "', $unknown)));
    }

    return [
      'html' => $value['html'] ?? null,
      'template' => $value['template'] ?? null,
      'component' => $value['component'] ?? null,
      'parameters' => $value['parameters'] ?? [],
    ];
  }

  /**
   * @param array<string, mixed> $options
   *
   * @return array<string, mixed>
   */
  private function resolveOptions(array $options): array
  {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);

    return $resolver->resolve($options);
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @return class-string|null
   */
  public function getSymfonyType(): ?string
  {
    return $this->symfonyType;
  }

  /**
   * A display only field is shown in the layout but is not added to the Symfony form.
   *
   * @return bool
   */
  public function isDisplayOnly(): bool
  {
    return $this->displayOnly;
  }

  /**
   * @return array<string, mixed>
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * @param string $key
   *
   * @return mixed
   */
  public function getOption(string $key): mixed
  {
    if (!\array_key_exists($key, $this->options))
    {
      throw new \InvalidArgumentException(\sprintf('The option "%s" does not exist on the field "%s".', $key, $this->name));
    }

    return $this->options[$key];
  }

  /**
   * Merges the given options with the current ones: the options that are not given keep their value.
   *
   * @param array<string, mixed> $options
   *
   * @return $this
   */
  public function setOptions(array $options): static
  {
    $this->rawOptions = array_replace($this->rawOptions, $options);
    $this->options = $this->resolveOptions($this->rawOptions);
    if (\array_key_exists('constraints', $options))
    {
      $this->setConstraints($this->options['constraints']);
    }

    return $this;
  }

  /**
   * @param string $key
   * @param mixed  $value
   *
   * @return $this
   */
  public function addOption(string $key, mixed $value): static
  {
    return $this->setOptions([$key => $value]);
  }

  /**
   * @return string|false|null
   */
  public function getLabel(): string|false|null
  {
    return $this->options['label'];
  }

  /**
   * @param string|false|null $label
   *
   * @return $this
   */
  public function setLabel(string|false|null $label): static
  {
    return $this->setOptions(['label' => $label]);
  }

  /**
   * @return bool
   */
  public function isRequired(): bool
  {
    return $this->options['required'];
  }

  /**
   * @param bool $required
   *
   * @return $this
   */
  public function setRequired(bool $required): static
  {
    return $this->setOptions(['required' => $required]);
  }

  /**
   * @return bool
   */
  public function isVisible(): bool
  {
    $visible = $this->options['visible'];
    if (\is_bool($visible))
    {
      return $visible;
    }
    if (\is_array($visible) && !\is_callable($visible))
    {
      $callable = array_shift($visible);
      if (!\is_callable($callable))
      {
        throw new \InvalidArgumentException(\sprintf('The first element of the "visible" option of the field "%s" must be callable.', $this->name));
      }

      return (bool) $callable(...$visible);
    }

    return (bool) $visible();
  }

  /**
   * @param bool|callable|array<int, mixed> $visible
   *
   * @return $this
   */
  public function setVisible(bool|callable|array $visible): static
  {
    return $this->setOptions(['visible' => $visible]);
  }

  /**
   * @return string|null
   */
  public function getSize(): ?string
  {
    return $this->options['size'];
  }

  /**
   * @param string|null $size
   *
   * @return $this
   */
  public function setSize(?string $size): static
  {
    return $this->setOptions(['size' => $size]);
  }

  /**
   * @return string|null
   */
  public function getTemplate(): ?string
  {
    return $this->options['template'];
  }

  /**
   * @param string|null $template
   *
   * @return $this
   */
  public function setTemplate(?string $template): static
  {
    return $this->setOptions(['template' => $template]);
  }

  /**
   * @return array<string, mixed>
   */
  public function getTemplateVars(): array
  {
    return $this->options['template_vars'];
  }

  /**
   * @param array<string, mixed> $templateVars
   *
   * @return $this
   */
  public function setTemplateVars(array $templateVars): static
  {
    return $this->setOptions(['template_vars' => $templateVars]);
  }

  /**
   * @return bool
   */
  public function hasAutoConstraints(): bool
  {
    return $this->options['auto_constraints'];
  }

  /**
   * Adds a constraint. A constraint of the same class is kept, unless $replace is true.
   *
   * @param Constraint $constraint
   * @param bool       $replace
   *
   * @return $this
   */
  public function addConstraint(Constraint $constraint, bool $replace = false): static
  {
    if ($this->hasConstraint($constraint::class))
    {
      if (!$replace)
      {
        return $this;
      }
      $this->removeConstraint($constraint::class);
    }
    $this->constraints[] = $constraint;
    $this->requiredByConstraint($constraint);

    return $this;
  }

  /**
   * @param array<mixed> $constraints Instances of Constraint; anything else is rejected
   *
   * @return $this
   */
  public function setConstraints(array $constraints): static
  {
    $this->constraints = [];
    foreach ($constraints as $constraint)
    {
      if (!$constraint instanceof Constraint)
      {
        throw new \InvalidArgumentException(\sprintf('The constraints of the field "%s" must be instances of "%s".', $this->name, Constraint::class));
      }
      $this->constraints[] = $constraint;
      $this->requiredByConstraint($constraint);
    }

    return $this;
  }

  /**
   * @param class-string<Constraint> $constraintClass
   *
   * @return $this
   */
  public function removeConstraint(string $constraintClass): static
  {
    $this->constraints = array_values(array_filter(
      $this->constraints,
      static fn (Constraint $constraint): bool => $constraint::class !== $constraintClass
    ));

    return $this;
  }

  /**
   * @param class-string<Constraint> $constraintClass
   *
   * @return bool
   */
  public function hasConstraint(string $constraintClass): bool
  {
    foreach ($this->constraints as $constraint)
    {
      if ($constraint::class === $constraintClass)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * @return list<Constraint>
   */
  public function getConstraints(): array
  {
    return $this->constraints;
  }

  /**
   * @param Constraint $constraint
   *
   * @return void
   */
  private function requiredByConstraint(Constraint $constraint): void
  {
    if ($constraint instanceof NotNull && !$this->options['required'])
    {
      $this->rawOptions['required'] = true;
      $this->options['required'] = true;
    }
  }

  /**
   * @return FormMapper|null
   */
  public function getFormMapper(): ?FormMapper
  {
    return $this->formMapper;
  }

  /**
   * Called by the FormMapper when the field is added.
   *
   * @param FormMapper|null $formMapper
   *
   * @return $this
   */
  public function setFormMapper(?FormMapper $formMapper): static
  {
    $this->formMapper = $formMapper;

    return $this;
  }

  /**
   * Constraints the field adds by itself when its option "auto_constraints" is true (File for an upload,
   * Regex for a password...), next to the ones of the constraint guesser.
   *
   * @return list<Constraint>
   */
  public function getAutoConstraints(): array
  {
    return [];
  }

  /**
   * Adds the field to the form being built (called by AustralFormType with the final options). A field that
   * needs form listeners (SelectField with tags) overrides it, calls the parent and adds them to $builder.
   *
   * @param FormBuilderInterface<mixed> $builder Builder of the parent form
   * @param array<string, mixed>        $options Options of the Symfony form type
   *
   * @return void
   */
  public function addToForm(FormBuilderInterface $builder, array $options): void
  {
    $builder->add($this->name, $this->getSymfonyType(), $options);
  }

  /**
   * Options given to the Symfony form type. The keys of "form_options" win over the shortcuts.
   *
   * @return array<string, mixed>
   */
  public function getSymfonyOptions(): array
  {
    $options = $this->options['form_options'];
    $options += [
      'label' => $this->options['label'],
      'required' => $this->options['required'],
      'mapped' => $this->options['mapped'],
    ];
    foreach (['getter', 'setter'] as $key)
    {
      if (null !== $this->options[$key] && !\array_key_exists($key, $options))
      {
        $options[$key] = $this->options[$key];
      }
    }
    $attr = array_replace($options['attr'] ?? [], $this->options['attr']);
    if (null !== $this->options['placeholder'] && !\array_key_exists('placeholder', $attr))
    {
      $attr['placeholder'] = $this->options['placeholder'];
    }
    $options['attr'] = $attr;
    if ($this->constraints)
    {
      $options['constraints'] = array_merge($options['constraints'] ?? [], $this->constraints);
    }

    return $options;
  }

  /**
   * Presentation exposed to the theme (contract of view, form.vars.austral).
   *
   * @return array<string, mixed>
   */
  public function getPresentation(): array
  {
    return [
      'picto' => $this->options['picto'],
      'helper' => $this->options['helper'],
      'size' => $this->options['size'],
      'class' => $this->options['class'],
      'container_class' => $this->options['container_class'],
      'field_class' => u(new \ReflectionClass($this)->getShortName())->camel()->toString(),
      'label_position' => $this->options['label_position'],
      'before' => $this->options['before'],
      'after' => $this->options['after'],
      'template' => $this->options['template'],
      'template_vars' => $this->options['template_vars'],
      'widget' => $this->getWidget(),
    ];
  }

  /**
   * Display data specific to the field (labels of a switch, options of a JS widget...), exposed as
   * form.vars.austral.widget. Empty for most fields.
   *
   * @return array<string, mixed>
   */
  protected function getWidget(): array
  {
    return [];
  }
}
