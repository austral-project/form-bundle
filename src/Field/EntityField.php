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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Entity Field.
 *
 * EntityType shown as a select (Choices.js).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class EntityField extends AbstractSelectField
{
  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = EntityType::class;

  /**
   * @param string               $name
   * @param class-string         $entityClass
   * @param array<string, mixed> $options
   */
  public function __construct(string $name, string $entityClass, array $options = [])
  {
    parent::__construct($name, ['entity_class' => $entityClass] + $options);
  }

  /**
   * @param string               $name
   * @param class-string         $entityClass
   * @param array<string, mixed> $options
   *
   * @return self
   */
  public static function create(string $name, string $entityClass, array $options = []): self
  {
    return new self($name, $entityClass, $options);
  }

  /**
   * The entity class is the option "entity_class" ("class" is the CSS class of the field, as on every field).
   *
   * {@inheritdoc}
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setRequired('entity_class');
    $resolver->setAllowedTypes('entity_class', 'string');
    $resolver->setDefaults([
      'query_builder' => null,
      'choice_label' => null,
    ]);
    $resolver->setAllowedTypes('query_builder', ['null', 'callable', 'Doctrine\ORM\QueryBuilder'])
      ->setAllowedTypes('choice_label', ['null', 'string', 'callable']);
  }

  /**
   * @return class-string
   */
  public function getEntityClass(): string
  {
    return $this->options['entity_class'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $options += ['class' => $this->options['entity_class']];
    foreach (['query_builder', 'choice_label'] as $key)
    {
      if (null !== $this->options[$key])
      {
        $options += [$key => $this->options[$key]];
      }
    }

    return $options;
  }
}
