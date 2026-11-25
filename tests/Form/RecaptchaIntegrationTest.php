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
use Austral\FormBundle\Field\RecaptchaField;
use Austral\FormBundle\Field\TextField;
use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Validator\RecaptchaValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;

/**
 * A contact form protected by reCAPTCHA.
 */
final class RecaptchaIntegrationTest extends TestCase
{
  public function testValidToken(): void
  {
    $form = $this->form(new MockResponse('{"success": true, "score": 0.9, "action": "contact"}'));
    $form->submit(['email' => 'a@b.c', RecaptchaField::FIELD_NAME => 'token']);

    self::assertTrue($form->isValid());
  }

  public function testRefusedToken(): void
  {
    $form = $this->form(new MockResponse('{"success": false, "error-codes": ["invalid-input-response"]}'));
    $form->submit(['email' => 'a@b.c', RecaptchaField::FIELD_NAME => 'token']);

    self::assertFalse($form->isValid());
    $errors = $form->get(RecaptchaField::FIELD_NAME)->getErrors();
    self::assertCount(1, $errors);
    self::assertSame('errors.captcha', $errors[0]->getMessageTemplate());
  }

  public function testMissingToken(): void
  {
    $form = $this->form(new MockResponse('{"success": true}'));
    $form->submit(['email' => 'a@b.c']);

    self::assertFalse($form->isValid());
  }

  /**
   * @param MockResponse $response
   *
   * @return FormInterface<mixed>
   */
  private function form(MockResponse $response): FormInterface
  {
    $recaptchaValidator = new RecaptchaValidator(new MockHttpClient($response), new RequestStack());
    $validator = Validation::createValidatorBuilder()
      ->setConstraintValidatorFactory(new ConstraintValidatorFactory([RecaptchaValidator::class => $recaptchaValidator]))
      ->getValidator();
    $mapper = new FormMapper('contact')
      ->add(TextField::create('email'))
      ->add(RecaptchaField::create(['public_key' => 'public', 'secret_key' => 'secret', 'action' => 'contact']));

    return Forms::createFormFactoryBuilder()
      ->addExtension(new ValidatorExtension($validator))
      ->addType(new AustralFormType(new DoctrineConstraintGuesser()))
      ->addTypeExtension(new PresentationTypeExtension())
      ->getFormFactory()
      ->createNamed('contact', AustralFormType::class, null, ['mapper' => $mapper]);
  }
}
