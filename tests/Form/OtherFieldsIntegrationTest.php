<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Form;

use Austral\FormBundle\Constraint\DoctrineConstraintGuesser;
use Austral\FormBundle\Field\ColorField;
use Austral\FormBundle\Field\DateField;
use Austral\FormBundle\Field\Field;
use Austral\FormBundle\Field\PasswordField;
use Austral\FormBundle\Field\TimeField;
use Austral\FormBundle\Field\UploadField;
use Austral\FormBundle\Field\WysiwygField;
use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\Account;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;

/**
 * Password, date, time, color, wysiwyg and upload fields in a real form.
 */
final class OtherFieldsIntegrationTest extends TestCase
{
  public function testRepeatedPassword(): void
  {
    $account = new Account();

    $form = $this->form($account, PasswordField::create('plainPassword'));
    $form->submit(['plainPassword' => ['first' => 'Secret123', 'second' => 'Secret123']]);

    self::assertTrue($form->isValid());
    self::assertSame('Secret123', $account->plainPassword);
  }

  public function testPasswordsThatDiffer(): void
  {
    $form = $this->form(new Account(), PasswordField::create('plainPassword'));
    $form->submit(['plainPassword' => ['first' => 'Secret123', 'second' => 'Other1234']]);

    self::assertFalse($form->isValid());
    self::assertSame('field.error.plainPassword.repeat', $form->get('plainPassword')->getErrors(true)[0]->getMessageTemplate());
  }

  public function testPasswordMustMatch(): void
  {
    $form = $this->form(new Account(), PasswordField::create('plainPassword'));
    $form->submit(['plainPassword' => ['first' => 'weak', 'second' => 'weak']]);

    self::assertFalse($form->isValid());
    self::assertSame('errors.plainPassword.regex', $form->get('plainPassword')->getErrors(true)[0]->getMessageTemplate());
  }

  /**
   * As in 3.x: an empty password is valid (the current one is kept by the application).
   */
  public function testEmptyPasswordIsValid(): void
  {
    $form = $this->form(new Account(), PasswordField::create('plainPassword'));
    $form->submit(['plainPassword' => ['first' => '', 'second' => '']]);

    self::assertTrue($form->isValid());
  }

  public function testDate(): void
  {
    $account = new Account();

    $form = $this->form($account, DateField::create('birthday'));
    $form->submit(['birthday' => '24/12/1990']);

    self::assertTrue($form->isValid());
    self::assertSame('1990-12-24', $account->birthday?->format('Y-m-d'));
    self::assertSame('single_text', $form->createView()['birthday']->vars['widget']);
  }

  public function testTime(): void
  {
    $account = new Account();

    $form = $this->form($account, TimeField::create('openingAt'));
    $form->submit(['openingAt' => '09:30']);

    self::assertTrue($form->isValid());
    self::assertSame('09:30', $account->openingAt?->format('H:i'));
  }

  public function testColorAndWysiwyg(): void
  {
    $account = new Account();

    $form = $this->form($account, ColorField::create('color'), WysiwygField::create('content'));
    $form->submit(['color' => '#ff0000', 'content' => '<p>Hello</p>']);

    self::assertSame('#ff0000', $account->color);
    self::assertSame('<p>Hello</p>', $account->content);
  }

  public function testFieldConstraintsAddedByTheFormType(): void
  {
    $form = $this->form(new Account(), PasswordField::create('plainPassword'), UploadField::create('avatar', ['max_size' => '1M']));

    self::assertSame([Regex::class], self::classes($form->get('plainPassword')->getConfig()->getOption('constraints')));
    self::assertSame([File::class], self::classes($form->get('avatar')->getConfig()->getOption('constraints')));
    self::assertSame('little', $this->form(new Account(), UploadField::create('avatar', ['block_size' => 'little']))->createView()['avatar']->vars['austral']['widget']['block_size']);
  }

  public function testNoFieldConstraintsWithoutAutoConstraints(): void
  {
    $form = $this->form(new Account(), UploadField::create('avatar', ['auto_constraints' => false]));

    self::assertSame([], $form->get('avatar')->getConfig()->getOption('constraints'));
  }

  /**
   * @param Account $account
   * @param Field   ...$fields
   *
   * @return FormInterface<mixed>
   */
  private function form(Account $account, Field ...$fields): FormInterface
  {
    $mapper = new FormMapper('form_austral', Account::class)->adds($fields);

    return Forms::createFormFactoryBuilder()
      ->addExtension(new ValidatorExtension(Validation::createValidator()))
      ->addType(new AustralFormType(new DoctrineConstraintGuesser()))
      ->addTypeExtension(new PresentationTypeExtension())
      ->getFormFactory()
      ->createNamed($mapper->getName(), AustralFormType::class, $account, ['mapper' => $mapper]);
  }

  /**
   * @param mixed $constraints
   *
   * @return list<string>
   */
  private static function classes(mixed $constraints): array
  {
    return array_map(static fn (object $constraint): string => $constraint::class, (array) $constraints);
  }
}
