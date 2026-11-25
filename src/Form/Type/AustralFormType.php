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

use Austral\FormBundle\Constraint\ConstraintGuesserInterface;
use Austral\FormBundle\Event\FieldBuildEvent;
use Austral\FormBundle\Event\FormEvents;
use Austral\FormBundle\Field\Field;
use Austral\FormBundle\Layout\LayoutNode;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\View\LayoutViewBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Austral Form Type.
 *
 * Builds a form from a FormMapper given in the option "mapper". The type has no state: one service serves every
 * form, the definition travels with the options.
 *
 *   $form = $formFactory->createNamed($mapper->getName(), AustralFormType::class, $data, ['mapper' => $mapper]);
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @extends AbstractType<mixed>
 */
final class AustralFormType extends AbstractType
{
  /**
   * Block prefix of every Austral form: the theme targets this single prefix.
   */
  public const string BLOCK_PREFIX = 'austral_form';

  /**
   * @param ConstraintGuesserInterface    $constraintGuesser
   * @param EventDispatcherInterface|null $dispatcher        Dispatches austral.form.field.build
   */
  public function __construct(
    private readonly ConstraintGuesserInterface $constraintGuesser,
    private readonly ?EventDispatcherInterface $dispatcher = null,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockPrefix(): string
  {
    return self::BLOCK_PREFIX;
  }

  /**
   * {@inheritdoc}
   */
  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setRequired('mapper');
    $resolver->setAllowedTypes('mapper', FormMapper::class);
    $resolver->setDefaults([
      'data_class' => static fn (Options $options): ?string => $options['mapper']->getDataClass(),
      'translation_domain' => static fn (Options $options): string => $options['mapper']->getTranslationDomain(),
      'validation_groups' => static fn (Options $options): ?array => $options['mapper']->getValidationGroups(),
    ]);
    $resolver->setDefault('entry_vars', null);
    $resolver->setAllowedTypes('entry_vars', ['null', \Closure::class]);
    $resolver->addNormalizer('constraints', function (Options $options, mixed $constraints): array {
      return array_merge(
        \is_array($constraints) ? $constraints : [$constraints],
        $this->constraintGuesser->guessClassConstraints($options['data_class'], $options['mapper']),
      );
    });
  }

  /**
   * Adds the visible fields, in layout order. A field that is not visible (or inside a node that is not
   * visible) is not added: it can neither be rendered nor submitted, so its data is never changed.
   *
   * {@inheritdoc}
   */
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    /** @var FormMapper $mapper */
    $mapper = $options['mapper'];
    foreach (self::visibleFields($mapper) as $field)
    {
      if ($field->isDisplayOnly())
      {
        continue;
      }
      if (null === $field->getSymfonyType())
      {
        throw new \LogicException(\sprintf('The field "%s" (%s) has no Symfony form type.', $field->getName(), $field::class));
      }
      // Same class kept by addConstraint(): building the form again adds nothing.
      foreach ($this->constraintGuesser->guessFieldConstraints($options['data_class'], $field) as $constraint)
      {
        $field->addConstraint($constraint);
      }
      if ($field->hasAutoConstraints())
      {
        foreach ($field->getAutoConstraints() as $constraint)
        {
          $field->addConstraint($constraint);
        }
      }
      $this->dispatcher?->dispatch(new FieldBuildEvent($mapper, $field, $builder), FormEvents::FIELD_BUILD);
      $field->addToForm($builder, $field->getSymfonyOptions() + ['austral' => $field->getPresentation()]);
    }
  }

  /**
   * novalidate on the <form> only (the root view), unless the mapper asks for the HTML5 validation.
   *
   * {@inheritdoc}
   */
  public function buildView(FormView $view, FormInterface $form, array $options): void
  {
    if (null === $view->parent && !$options['mapper']->isHtml5Validate())
    {
      $view->vars['attr'] += ['novalidate' => 'novalidate'];
    }
  }

  /**
   * The layout needs the views of the children: finishView(), not buildView(). An entry of a collection also
   * gets form.vars.austral_entry, from the option "entry_vars" given by CollectionEmbedField (its position...).
   *
   * {@inheritdoc}
   */
  public function finishView(FormView $view, FormInterface $form, array $options): void
  {
    $data = $form->getData();
    $data = \is_object($data) ? $data : null;
    $view->vars['austral_layout'] = LayoutViewBuilder::build($options['mapper'], $view, $data);
    if (null !== $options['entry_vars'])
    {
      $view->vars['austral_entry'] = ($options['entry_vars'])($data);
    }
  }

  /**
   * @param FormMapper $mapper
   *
   * @return list<Field>
   */
  private static function visibleFields(FormMapper $mapper): array
  {
    $fields = [];
    $walk = static function (LayoutNode $node) use (&$walk, &$fields, $mapper): void {
      if (!$node->isVisible())
      {
        return;
      }
      foreach ($node->getChildren() as $child)
      {
        if ($child instanceof LayoutNode)
        {
          $walk($child);
        }
        elseif (($field = $mapper->getField($child)) && $field->isVisible())
        {
          $fields[] = $field;
        }
      }
    };
    foreach ($mapper->getNodes() as $node)
    {
      $walk($node);
    }

    return $fields;
  }
}
