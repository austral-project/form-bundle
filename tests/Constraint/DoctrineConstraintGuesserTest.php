<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Constraint;

use Austral\FormBundle\Constraint\DoctrineConstraintGuesser;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\Article;
use Austral\FormBundle\Tests\Fixtures\ArticleStatus;
use Austral\FormBundle\Tests\Fixtures\TemplateLikeField;
use Austral\FormBundle\Tests\Fixtures\TextareaLikeField;
use Austral\FormBundle\Tests\Fixtures\TextLikeField;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

final class DoctrineConstraintGuesserTest extends TestCase
{
  public function testWithoutRegistryOnlyTheRequiredRuleApplies(): void
  {
    $guesser = new DoctrineConstraintGuesser();

    self::assertSame([], $guesser->guessFieldConstraints(Article::class, TextLikeField::create('title')));

    $constraints = $guesser->guessFieldConstraints(Article::class, TextLikeField::create('title', ['required' => true]));
    self::assertSame([NotNull::class], self::classes($constraints));
    self::assertInstanceOf(NotNull::class, $constraints[0]);
    self::assertSame('errors.not_null', $constraints[0]->message);
  }

  public function testWithoutDataClassOnlyTheRequiredRuleApplies(): void
  {
    $guesser = $this->guesser();

    self::assertSame([], $guesser->guessFieldConstraints(null, TextLikeField::create('title')));
    self::assertSame([NotNull::class], self::classes($guesser->guessFieldConstraints(null, TextLikeField::create('title', ['required' => true]))));
  }

  public function testColumnNotNullableMakesTheFieldRequired(): void
  {
    $constraints = $this->guesser()->guessFieldConstraints(Article::class, TextareaLikeField::create('summary'));

    self::assertSame([NotNull::class], self::classes($constraints));
  }

  public function testNullableColumnAddsNothing(): void
  {
    self::assertSame([], $this->guesser()->guessFieldConstraints(Article::class, TextareaLikeField::create('publishedAt')));
  }

  public function testStringColumnAddsLengthMax(): void
  {
    $constraints = $this->guesser()->guessFieldConstraints(Article::class, TextareaLikeField::create('title'));

    self::assertSame([Length::class, NotNull::class], self::classes($constraints));
    self::assertInstanceOf(Length::class, $constraints[0]);
    self::assertSame(120, $constraints[0]->max);
    self::assertSame('errors.length.max', $constraints[0]->maxMessage);
  }

  /**
   * In 3.x, a string column without length gave Length(max: null), which throws: no Length now.
   */
  public function testStringColumnWithoutLengthAddsNoLength(): void
  {
    self::assertSame([], $this->guesser()->guessFieldConstraints(Article::class, TextareaLikeField::create('slug')));
  }

  public function testEnumColumnAddsNoLength(): void
  {
    self::assertSame([], $this->guesser()->guessFieldConstraints(Article::class, TextareaLikeField::create('status')));
  }

  /**
   * @return iterable<string, array{string, class-string<Constraint>, string}>
   */
  public static function textTypes(): iterable
  {
    yield 'string' => ['slug', Type::class, 'errors.type.string'];
    yield 'integer' => ['views', Type::class, 'errors.type.integer'];
    yield 'float' => ['rating', Type::class, 'errors.type.float'];
    yield 'datetime' => ['publishedAt', DateTime::class, 'errors.type.datetime'];
  }

  /**
   * @param string                   $property
   * @param class-string<Constraint> $constraintClass
   * @param string                   $message
   */
  #[DataProvider('textTypes')]
  public function testTextFieldGetsTheTypeOfItsColumn(string $property, string $constraintClass, string $message): void
  {
    $constraints = $this->guesser()->guessFieldConstraints(Article::class, TextLikeField::create($property));

    self::assertSame([$constraintClass], self::classes($constraints));
    $constraint = $constraints[0];
    self::assertTrue($constraint instanceof Type || $constraint instanceof DateTime);
    self::assertSame($message, $constraint->message);
  }

  public function testTypeOfTheTextField(): void
  {
    $guesser = $this->guesser();

    $views = $guesser->guessFieldConstraints(Article::class, TextLikeField::create('views'));
    self::assertInstanceOf(Type::class, $views[0]);
    self::assertSame('integer', $views[0]->type);

    $rating = $guesser->guessFieldConstraints(Article::class, TextLikeField::create('rating'));
    self::assertInstanceOf(Type::class, $rating[0]);
    self::assertSame('numeric', $rating[0]->type);
  }

  public function testOtherFieldsGetNoType(): void
  {
    self::assertSame([], $this->guesser()->guessFieldConstraints(Article::class, TextareaLikeField::create('views')));
  }

  public function testTextFieldOnStringColumnGetsLengthTypeAndNotNull(): void
  {
    $constraints = $this->guesser()->guessFieldConstraints(Article::class, TextLikeField::create('title'));

    self::assertSame([Length::class, Type::class, NotNull::class], self::classes($constraints));
  }

  public function testAutoConstraintsDisabled(): void
  {
    $field = TextLikeField::create('title', ['auto_constraints' => false, 'required' => true]);

    self::assertSame([], $this->guesser()->guessFieldConstraints(Article::class, $field));
  }

  public function testUnmappedFieldIgnoresDoctrine(): void
  {
    self::assertSame([], $this->guesser()->guessFieldConstraints(Article::class, TextLikeField::create('title', ['mapped' => false])));
  }

  public function testPropertyUnknownToDoctrine(): void
  {
    self::assertSame([], $this->guesser()->guessFieldConstraints(Article::class, TextLikeField::create('unknown')));
  }

  public function testClassNotManagedByDoctrine(): void
  {
    $registry = $this->createStub(ManagerRegistry::class);
    $registry->method('getManagerForClass')->willReturn(null);
    $guesser = new DoctrineConstraintGuesser($registry);

    self::assertSame([], $guesser->guessFieldConstraints(Article::class, TextLikeField::create('title')));
  }

  public function testDisplayOnlyFieldGetsNothing(): void
  {
    $field = TemplateLikeField::create('title', 'info.html.twig', ['required' => true]);

    self::assertSame([], $this->guesser()->guessFieldConstraints(Article::class, $field));
  }

  public function testUniqueColumnOfATextFieldAddsUniqueEntity(): void
  {
    $mapper = new FormMapper('form', Article::class)
      ->add(TextLikeField::create('slug'))
      ->add(TextLikeField::create('title'));

    $constraints = $this->guesser()->guessClassConstraints(Article::class, $mapper);

    self::assertCount(1, $constraints);
    self::assertInstanceOf(UniqueEntity::class, $constraints[0]);
    self::assertSame('slug', $constraints[0]->fields);
    self::assertSame('form.errors.unique', $constraints[0]->message);
  }

  public function testUniqueEntityOnlyForTextFieldsWithAutoConstraints(): void
  {
    $guesser = $this->guesser();

    $mapper = new FormMapper('form', Article::class)->add(TextareaLikeField::create('slug'));
    self::assertSame([], $guesser->guessClassConstraints(Article::class, $mapper));

    $mapper = new FormMapper('form', Article::class)->add(TextLikeField::create('slug', ['auto_constraints' => false]));
    self::assertSame([], $guesser->guessClassConstraints(Article::class, $mapper));

    self::assertSame([], new DoctrineConstraintGuesser()->guessClassConstraints(Article::class, new FormMapper()->add(TextLikeField::create('slug'))));
  }

  /**
   * Mapping of Article: title string(120) not null, slug string unique nullable, summary text not null,
   * status enum, views integer, rating float, publishedAt datetime nullable.
   *
   * @return DoctrineConstraintGuesser
   */
  private function guesser(): DoctrineConstraintGuesser
  {
    $metadata = new ClassMetadata(Article::class);
    $metadata->mapField(['fieldName' => 'title', 'type' => 'string', 'length' => 120, 'nullable' => false]);
    $metadata->mapField(['fieldName' => 'slug', 'type' => 'string', 'nullable' => true, 'unique' => true]);
    $metadata->mapField(['fieldName' => 'summary', 'type' => 'text', 'nullable' => false]);
    $metadata->mapField(['fieldName' => 'status', 'type' => 'string', 'length' => 20, 'nullable' => true, 'enumType' => ArticleStatus::class]);
    $metadata->mapField(['fieldName' => 'views', 'type' => 'integer', 'nullable' => true]);
    $metadata->mapField(['fieldName' => 'rating', 'type' => 'float', 'nullable' => true]);
    $metadata->mapField(['fieldName' => 'publishedAt', 'type' => 'datetime', 'nullable' => true]);

    $manager = $this->createStub(ObjectManager::class);
    $manager->method('getClassMetadata')->willReturn($metadata);
    $registry = $this->createStub(ManagerRegistry::class);
    $registry->method('getManagerForClass')->willReturn($manager);

    return new DoctrineConstraintGuesser($registry);
  }

  /**
   * @param list<Constraint> $constraints
   *
   * @return list<class-string<Constraint>>
   */
  private static function classes(array $constraints): array
  {
    return array_map(static fn (Constraint $constraint): string => $constraint::class, $constraints);
  }
}
