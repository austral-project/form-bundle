<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Field;

use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\TextLikeField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class FieldTest extends TestCase
{
  public function testDefaults(): void
  {
    $field = TextLikeField::create('title');

    self::assertSame('title', $field->getName());
    self::assertSame(TextType::class, $field->getSymfonyType());
    self::assertSame('fields.title.entitled', $field->getLabel());
    self::assertFalse($field->isRequired());
    self::assertTrue($field->isVisible());
    self::assertTrue($field->hasAutoConstraints());
    self::assertFalse($field->isDisplayOnly());
    self::assertSame([], $field->getConstraints());
    self::assertNull($field->getFormMapper());
  }

  public function testLabelCanBeFalseButNotTrue(): void
  {
    self::assertFalse(TextLikeField::create('title', ['label' => false])->getLabel());

    $this->expectException(InvalidOptionsException::class);
    TextLikeField::create('title', ['label' => true]);
  }

  public function testUnknownOptionIsRejected(): void
  {
    $this->expectException(UndefinedOptionsException::class);
    TextLikeField::create('title', ['entitled' => 'Title']);
  }

  public function testGetOptionRejectsAnUnknownKey(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    TextLikeField::create('title')->getOption('unknown');
  }

  public function testSetOptionsMergesWithTheCurrentOptions(): void
  {
    $field = TextLikeField::create('title', ['picto' => 'austral-picto-text', 'helper' => 'first']);
    $field->setOptions(['helper' => 'second']);

    self::assertSame('austral-picto-text', $field->getOption('picto'));
    self::assertSame('second', $field->getOption('helper'));
  }

  public function testSettersUpdateTheOptions(): void
  {
    $field = TextLikeField::create('title')
      ->setLabel('custom.label')
      ->setRequired(true)
      ->setSize('col--6')
      ->setTemplate('text.html.twig')
      ->setTemplateVars(['a' => 1])
      ->addOption('picto', 'austral-picto-edit');

    self::assertSame('custom.label', $field->getLabel());
    self::assertTrue($field->isRequired());
    self::assertSame('col--6', $field->getSize());
    self::assertSame('text.html.twig', $field->getTemplate());
    self::assertSame(['a' => 1], $field->getTemplateVars());
    self::assertSame('austral-picto-edit', $field->getOption('picto'));
  }

  public function testAConstraintOfTheSameClassIsKeptUnlessReplaced(): void
  {
    $first = new Length(max: 10);
    $second = new Length(max: 20);
    $field = TextLikeField::create('title')->addConstraint($first)->addConstraint($second);

    self::assertSame([$first], $field->getConstraints());

    $field->addConstraint($second, true);
    self::assertSame([$second], $field->getConstraints());
  }

  public function testNotNullMakesTheFieldRequired(): void
  {
    self::assertTrue(TextLikeField::create('title')->addConstraint(new NotNull())->isRequired());
    self::assertTrue(TextLikeField::create('title')->setConstraints([new NotNull()])->isRequired());
    self::assertTrue(TextLikeField::create('title', ['constraints' => [new NotNull()]])->isRequired());
  }

  public function testRequiredStaysAfterSetOptionsWhenANotNullConstraintWasAdded(): void
  {
    $field = TextLikeField::create('title')->addConstraint(new NotNull());
    $field->setOptions(['helper' => 'help']);

    self::assertTrue($field->isRequired());
  }

  public function testConstraintsCanBeRemovedAndChecked(): void
  {
    $field = TextLikeField::create('title')->setConstraints([new Length(max: 10), new NotBlank()]);

    self::assertTrue($field->hasConstraint(Length::class));
    $field->removeConstraint(Length::class);
    self::assertFalse($field->hasConstraint(Length::class));
    self::assertCount(1, $field->getConstraints());
    self::assertInstanceOf(NotBlank::class, $field->getConstraints()[0]);
  }

  public function testSetConstraintsRejectsAnythingElseThanAConstraint(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    TextLikeField::create('title')->setConstraints(['not a constraint']);
  }

  public function testSymfonyOptionsFromTheShortcuts(): void
  {
    $getter = static fn (object $object): string => 'value';
    $constraint = new Length(max: 10);
    $field = TextLikeField::create('title', [
      'required' => true,
      'attr' => ['class' => 'input'],
      'placeholder' => 'placeholder.title',
      'getter' => $getter,
      'constraints' => [$constraint],
    ]);

    self::assertSame([
      'label' => 'fields.title.entitled',
      'required' => true,
      'mapped' => true,
      'getter' => $getter,
      'attr' => ['class' => 'input', 'placeholder' => 'placeholder.title'],
      'constraints' => [$constraint],
    ], $field->getSymfonyOptions());
  }

  public function testFormOptionsWinOverTheShortcuts(): void
  {
    $field = TextLikeField::create('title', [
      'required' => true,
      'attr' => ['class' => 'from-field'],
      'placeholder' => 'placeholder.title',
      'form_options' => [
        'required' => false,
        'attr' => ['class' => 'from-form-options', 'placeholder' => 'kept', 'data-x' => '1'],
        'empty_data' => '',
      ],
    ]);
    $options = $field->getSymfonyOptions();

    self::assertFalse($options['required']);
    self::assertSame('', $options['empty_data']);
    self::assertSame(['class' => 'from-field', 'placeholder' => 'kept', 'data-x' => '1'], $options['attr']);
    self::assertArrayNotHasKey('getter', $options);
    self::assertArrayNotHasKey('setter', $options);
    self::assertArrayNotHasKey('constraints', $options);
  }

  public function testConstraintsOfFormOptionsAreKept(): void
  {
    $fromFormOptions = new NotBlank();
    $fromField = new Length(max: 10);
    $field = TextLikeField::create('title', ['form_options' => ['constraints' => [$fromFormOptions]]])->addConstraint($fromField);

    self::assertSame([$fromFormOptions, $fromField], $field->getSymfonyOptions()['constraints']);
  }

  public function testVisibleAcceptsABooleanAClosureOrACallableWithArguments(): void
  {
    self::assertFalse(TextLikeField::create('title', ['visible' => false])->isVisible());
    self::assertFalse(TextLikeField::create('title', ['visible' => static fn (): bool => false])->isVisible());
    self::assertTrue(TextLikeField::create('title', ['visible' => [static fn (string $role): bool => 'ROLE_ROOT' === $role, 'ROLE_ROOT']])->isVisible());
    self::assertFalse(TextLikeField::create('title')->setVisible(false)->isVisible());
  }

  public function testSlotsAreNormalized(): void
  {
    $field = TextLikeField::create('title', ['before' => '<span>€</span>', 'after' => ['template' => 'after.html.twig']]);

    self::assertSame(['html' => '<span>€</span>', 'template' => null, 'component' => null, 'parameters' => []], $field->getOption('before'));
    self::assertSame(['html' => null, 'template' => 'after.html.twig', 'component' => null, 'parameters' => []], $field->getOption('after'));
  }

  public function testAnUnknownSlotKeyIsRejected(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    TextLikeField::create('title', ['before' => ['path' => 'old.html.twig']]);
  }

  public function testPresentation(): void
  {
    $presentation = TextLikeField::create('title', ['picto' => 'austral-picto-text', 'container_class' => 'large'])->getPresentation();

    self::assertSame('austral-picto-text', $presentation['picto']);
    self::assertSame('large', $presentation['container_class']);
    self::assertSame('textLikeField', $presentation['field_class']);
    self::assertSame('external', $presentation['label_position']);
    self::assertNull($presentation['before']);
  }

  public function testFormMapperCanBeSetAndUnset(): void
  {
    $mapper = new FormMapper();
    $field = TextLikeField::create('title')->setFormMapper($mapper);

    self::assertSame($mapper, $field->getFormMapper());
    self::assertNull($field->setFormMapper(null)->getFormMapper());
  }
}
