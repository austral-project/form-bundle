<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Presentation Type Extension.
 *
 * Option "austral" on every form type, exposed to the theme as form.vars.austral (view contract of a field).
 * The Austral fields fill it from Field::getPresentation(); a plain Symfony type can use it too.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class PresentationTypeExtension extends AbstractTypeExtension
{
  /**
   * Keys of form.vars.austral, with their default value.
   */
  public const array DEFAULTS = [
    'picto' => null,
    'helper' => null,
    'size' => null,
    'class' => null,
    'container_class' => null,
    'field_class' => null,
    'label_position' => 'external',
    'before' => null,
    'after' => null,
    'template' => null,
    'template_vars' => [],
    'widget' => [],
  ];

  public static function getExtendedTypes(): iterable
  {
    return [FormType::class];
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefault('austral', null);
    $resolver->setAllowedTypes('austral', ['null', 'array']);
    $resolver->setNormalizer('austral', static function (Options $options, ?array $value): ?array {
      if (null === $value)
      {
        return null;
      }
      if ($unknown = array_diff_key($value, self::DEFAULTS))
      {
        throw new InvalidOptionsException(\sprintf('The option "austral" has unknown keys "%s". Known keys: "%s".', implode('", "', array_keys($unknown)), implode('", "', array_keys(self::DEFAULTS))));
      }

      return array_replace(self::DEFAULTS, $value);
    });
  }

  public function buildView(FormView $view, FormInterface $form, array $options): void
  {
    $view->vars['austral'] = $options['austral'];
  }
}
