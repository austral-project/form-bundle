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

use Austral\FormBundle\Field\PasswordField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Regex;

final class PasswordFieldTest extends TestCase
{
  public function testRepeatedByDefault(): void
  {
    $field = PasswordField::create('plainPassword');
    $options = $field->getSymfonyOptions();
    $attr = ['autocomplete' => 'new-password', 'data-password' => ''];

    self::assertSame(RepeatedType::class, $field->getSymfonyType());
    self::assertSame(PasswordType::class, $options['type']);
    self::assertSame('fields.plainPassword.entitled.master', $options['label']);
    self::assertSame(['label' => 'fields.plainPassword.entitled.master.first', 'attr' => $attr], $options['first_options']);
    self::assertSame(['label' => 'fields.plainPassword.entitled.master.second', 'attr' => $attr], $options['second_options']);
    self::assertSame('field.error.plainPassword.repeat', $options['invalid_message']);
    self::assertTrue($options['required']);
    self::assertFalse($field->isRequired());
  }

  public function testSingleInput(): void
  {
    $field = PasswordField::create('plainPassword', ['repeat' => false, 'label' => 'Password']);
    $options = $field->getSymfonyOptions();

    self::assertSame(PasswordType::class, $field->getSymfonyType());
    self::assertSame('Password', $options['label']);
    self::assertSame(['autocomplete' => 'new-password', 'data-password' => ''], $options['attr']);
    self::assertArrayNotHasKey('first_options', $options);
  }

  public function testNoLabel(): void
  {
    $options = PasswordField::create('plainPassword', ['label' => false])->getSymfonyOptions();

    self::assertFalse($options['label']);
    self::assertFalse($options['first_options']['label']);
  }

  public function testFormOptionsWin(): void
  {
    $options = PasswordField::create('plainPassword', ['form_options' => ['invalid_message' => 'mismatch', 'first_options' => ['label' => 'A']]])->getSymfonyOptions();

    self::assertSame('mismatch', $options['invalid_message']);
    self::assertSame(['label' => 'A'], $options['first_options']);
  }

  public function testRegexFromMatch(): void
  {
    $constraints = PasswordField::create('plainPassword')->getAutoConstraints();

    self::assertCount(1, $constraints);
    self::assertInstanceOf(Regex::class, $constraints[0]);
    self::assertSame(PasswordField::MATCH_DEFAULT, $constraints[0]->pattern);
    self::assertSame('errors.plainPassword.regex', $constraints[0]->message);
    self::assertSame([], PasswordField::create('plainPassword', ['match' => null])->getAutoConstraints());
  }

  public function testWithoutAutoConstraintsNotRequiredInHtml(): void
  {
    self::assertFalse(PasswordField::create('plainPassword', ['auto_constraints' => false])->getSymfonyOptions()['required']);
  }

  public function testWidget(): void
  {
    self::assertSame(['repeat' => true, 'type_class' => 'passwordType'], PasswordField::create('plainPassword')->getPresentation()['widget']);
  }
}
