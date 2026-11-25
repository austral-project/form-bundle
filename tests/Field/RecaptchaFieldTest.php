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

use Austral\FormBundle\Field\RecaptchaField;
use Austral\FormBundle\Validator\Recaptcha;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

final class RecaptchaFieldTest extends TestCase
{
  public function testHiddenUnmappedField(): void
  {
    $field = RecaptchaField::create(['public_key' => 'public', 'secret_key' => 'secret']);
    $options = $field->getSymfonyOptions();

    self::assertSame(RecaptchaField::FIELD_NAME, $field->getName());
    self::assertSame(HiddenType::class, $field->getSymfonyType());
    self::assertFalse($options['mapped']);
    self::assertFalse($options['label']);
    self::assertFalse($options['error_bubbling']);
    self::assertSame('public', $options['attr']['data-recaptcha']);
    self::assertSame('registry', $options['attr']['data-type-action']);
    self::assertSame('public', $field->getPublicKey());
    self::assertSame('registry', $field->getAction());
  }

  public function testConstraintFromTheOptions(): void
  {
    $field = RecaptchaField::create(['public_key' => 'public', 'secret_key' => 'secret', 'action' => 'contact', 'score' => ['limit' => 0.5]]);
    $constraints = $field->getSymfonyOptions()['constraints'];

    self::assertCount(1, $constraints);
    self::assertInstanceOf(Recaptcha::class, $constraints[0]);
    self::assertSame('secret', $constraints[0]->secretKey);
    self::assertSame('contact', $constraints[0]->action);
    self::assertSame(0.5, $constraints[0]->scoreLimit);
    self::assertSame('bot-detected', $constraints[0]->scoreMessage);
    self::assertSame('errors.captcha', $constraints[0]->message);
  }

  public function testAlwaysChecked(): void
  {
    $field = RecaptchaField::create(['public_key' => 'public', 'secret_key' => 'secret', 'auto_constraints' => false]);

    self::assertInstanceOf(Recaptcha::class, $field->getSymfonyOptions()['constraints'][0]);
  }

  public function testKeysRequired(): void
  {
    $this->expectException(MissingOptionsException::class);

    RecaptchaField::create(['public_key' => 'public']);
  }
}
