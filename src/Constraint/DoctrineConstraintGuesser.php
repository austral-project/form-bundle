<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Constraint;

use Austral\FormBundle\Field\Field;
use Austral\FormBundle\Mapper\FormMapper;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\FieldMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Austral Doctrine Constraint Guesser.
 *
 * Constraints deduced from the Doctrine mapping of the data class, as the 3.x FormSubscriber did:
 *  - a required field, or a column that is not nullable: NotNull (the field becomes required);
 *  - a string column with a length (not an enum): Length(max);
 *  - a text field: Type (string, integer, numeric) or DateTime, from the column type;
 *  - a text field on a unique column: UniqueEntity on the form.
 *
 * Without a registry, or for a class or a property Doctrine does not manage, only the first rule applies.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class DoctrineConstraintGuesser implements ConstraintGuesserInterface
{
  /**
   * Doctrine column type => Type constraint, for the text fields.
   */
  private const array TYPES = [
    'string' => 'string',
    'integer' => 'integer',
    'float' => 'numeric',
  ];

  /**
   * @param ManagerRegistry|null $registry
   */
  public function __construct(
    private readonly ?ManagerRegistry $registry = null,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function guessFieldConstraints(?string $dataClass, Field $field): array
  {
    if (!$field->hasAutoConstraints() || $field->isDisplayOnly())
    {
      return [];
    }
    $constraints = [];
    $mapping = $this->fieldMapping($dataClass, $field);
    if ($mapping)
    {
      if ('string' === $mapping->type && null === $mapping->enumType && $mapping->length > 0)
      {
        $constraints[] = new Length(max: $mapping->length, maxMessage: 'errors.length.max');
      }
      if (self::isTextField($field))
      {
        if (\array_key_exists($mapping->type, self::TYPES))
        {
          $constraints[] = new Type(type: self::TYPES[$mapping->type], message: "errors.type.{$mapping->type}");
        }
        elseif ('datetime' === $mapping->type)
        {
          $constraints[] = new DateTime(message: 'errors.type.datetime');
        }
      }
    }
    if ($field->isRequired() || false === $mapping?->nullable)
    {
      $constraints[] = new NotNull(message: 'errors.not_null');
    }

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function guessClassConstraints(?string $dataClass, FormMapper $mapper): array
  {
    $constraints = [];
    foreach ($mapper->getFields() as $field)
    {
      if (!$field->hasAutoConstraints() || $field->isDisplayOnly() || !self::isTextField($field))
      {
        continue;
      }
      if (true === $this->fieldMapping($dataClass, $field)?->unique)
      {
        $constraints[] = new UniqueEntity(fields: $field->getName(), message: 'form.errors.unique');
      }
    }

    return $constraints;
  }

  /**
   * Doctrine mapping of the property behind the field, null when there is none.
   *
   * @param class-string|null $dataClass
   * @param Field             $field
   *
   * @return FieldMapping|null
   */
  private function fieldMapping(?string $dataClass, Field $field): ?FieldMapping
  {
    if (null === $dataClass || null === $this->registry || !$field->getOption('mapped'))
    {
      return null;
    }
    $metadata = $this->registry->getManagerForClass($dataClass)?->getClassMetadata($dataClass);
    if (!$metadata instanceof ClassMetadata || !$metadata->hasField($field->getName()))
    {
      return null;
    }

    return $metadata->getFieldMapping($field->getName());
  }

  /**
   * The type rules of the 3.x only applied to TextField: a field built on TextType (PHP inheritance, so not
   * TextareaType or EmailType, which only declare TextType as their parent form type).
   *
   * @param Field $field
   *
   * @return bool
   */
  private static function isTextField(Field $field): bool
  {
    return null !== $field->getSymfonyType() && is_a($field->getSymfonyType(), TextType::class, true);
  }
}
