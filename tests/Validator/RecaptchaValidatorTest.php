<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Validator;

use Austral\FormBundle\Validator\Recaptcha;
use Austral\FormBundle\Validator\RecaptchaValidator;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends ConstraintValidatorTestCase<RecaptchaValidator>
 */
final class RecaptchaValidatorTest extends ConstraintValidatorTestCase
{
  /**
   * @var list<array{string, string, array<string, mixed>}>
   */
  private array $requests = [];

  /**
   * @var list<MockResponse|\Throwable>
   */
  private array $responses = [];

  private ?TranslatorInterface $translator = null;

  protected function createValidator(): ConstraintValidatorInterface
  {
    $client = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
      $this->requests[] = [$method, $url, $options];
      $response = array_shift($this->responses) ?? new MockResponse('{"success": true}');
      if ($response instanceof \Throwable)
      {
        throw $response;
      }

      return $response;
    });
    $requestStack = new RequestStack();
    $requestStack->push(Request::create('/', 'POST', [], [], [], ['REMOTE_ADDR' => '203.0.113.7']));

    return new RecaptchaValidator($client, $requestStack, $this->translator);
  }

  public function testValid(): void
  {
    $this->validator->validate('token', new Recaptcha(secretKey: 'secret'));

    $this->assertNoViolation();
    self::assertCount(1, $this->requests);
    [$method, $url, $options] = $this->requests[0];
    self::assertSame('POST', $method);
    self::assertSame(RecaptchaValidator::VERIFY_URL, $url);
    self::assertSame('secret=secret&response=token&remoteip=203.0.113.7', $options['body']);
  }

  public function testTlsIsVerified(): void
  {
    $this->validator->validate('token', new Recaptcha(secretKey: 'secret'));

    $options = $this->requests[0][2];
    self::assertTrue($options['verify_peer'] ?? true);
    self::assertTrue($options['verify_host'] ?? true);
  }

  public function testFailure(): void
  {
    $this->responses[] = new MockResponse('{"success": false, "error-codes": ["invalid-input-response", "timeout-or-duplicate"]}');

    $this->validator->validate('token', new Recaptcha(secretKey: 'secret', message: 'errors.captcha'));

    $this->buildViolation('errors.captcha')
      ->setParameter('{{ reason }}', 'recaptcha.error.invalid-input-response, recaptcha.error.timeout-or-duplicate')
      ->assertRaised();
  }

  public function testFailureWithoutCodes(): void
  {
    $this->responses[] = new MockResponse('{"success": false}');

    $this->validator->validate('token', new Recaptcha(secretKey: 'secret'));

    $this->buildViolation('The Captcha is not valid : {{ reason }}.')
      ->setParameter('{{ reason }}', 'recaptcha.error.invalid-input-response')
      ->assertRaised();
  }

  public function testScoreUnderTheLimit(): void
  {
    $this->responses[] = new MockResponse('{"success": true, "score": 0.3, "action": "registry"}');

    $this->validator->validate('token', new Recaptcha(secretKey: 'secret', action: 'registry', scoreLimit: 0.5));

    $this->buildViolation('The Captcha is not valid : {{ reason }}.')
      ->setParameter('{{ reason }}', 'recaptcha.error.bot-detected')
      ->assertRaised();
  }

  public function testScoreOverTheLimit(): void
  {
    $this->responses[] = new MockResponse('{"success": true, "score": 0.9, "action": "registry"}');

    $this->validator->validate('token', new Recaptcha(secretKey: 'secret', action: 'registry', scoreLimit: 0.5));

    $this->assertNoViolation();
  }

  public function testAnotherAction(): void
  {
    $this->responses[] = new MockResponse('{"success": true, "score": 0.9, "action": "login"}');

    $this->validator->validate('token', new Recaptcha(secretKey: 'secret', action: 'registry'));

    $this->buildViolation('The Captcha is not valid : {{ reason }}.')
      ->setParameter('{{ reason }}', 'recaptcha.error.action-mismatch')
      ->assertRaised();
  }

  /**
   * In 3.x, a form without secret key was not checked at all.
   */
  public function testRefusedWithoutSecretKey(): void
  {
    $this->validator->validate('token', new Recaptcha());

    self::assertSame([], $this->requests);
    $this->buildViolation('The Captcha is not valid : {{ reason }}.')
      ->setParameter('{{ reason }}', 'recaptcha.error.missing-input-secret')
      ->assertRaised();
  }

  public function testRefusedWithoutToken(): void
  {
    $this->validator->validate(null, new Recaptcha(secretKey: 'secret'));

    self::assertSame([], $this->requests);
    $this->buildViolation('The Captcha is not valid : {{ reason }}.')
      ->setParameter('{{ reason }}', 'recaptcha.error.missing-input-response')
      ->assertRaised();
  }

  public function testRefusedWhenGoogleCannotBeReached(): void
  {
    $this->responses[] = new TransportException('Timeout');

    $this->validator->validate('token', new Recaptcha(secretKey: 'secret'));

    $this->buildViolation('The Captcha is not valid : {{ reason }}.')
      ->setParameter('{{ reason }}', 'recaptcha.error.unavailable')
      ->assertRaised();
  }

  public function testRefusedOnAnErrorStatus(): void
  {
    $this->responses[] = new MockResponse('{"success": true}', ['http_code' => 500]);

    $this->validator->validate('token', new Recaptcha(secretKey: 'secret'));

    $this->buildViolation('The Captcha is not valid : {{ reason }}.')
      ->setParameter('{{ reason }}', 'recaptcha.error.unavailable')
      ->assertRaised();
  }

  public function testRefusedOnAnUnreadableAnswer(): void
  {
    $this->responses[] = new MockResponse('<html>');

    $this->validator->validate('token', new Recaptcha(secretKey: 'secret'));

    $this->buildViolation('The Captcha is not valid : {{ reason }}.')
      ->setParameter('{{ reason }}', 'recaptcha.error.unavailable')
      ->assertRaised();
  }

  public function testReasonsTranslated(): void
  {
    $translator = $this->createMock(TranslatorInterface::class);
    $translator->expects(self::once())->method('trans')->with('recaptcha.error.bot-detected', [], 'validators')->willReturn('Robot');
    $this->translator = $translator;
    $this->validator = $this->createValidator();
    $this->validator->initialize($this->context);
    $this->responses[] = new MockResponse('{"success": true, "score": 0.1}');

    $this->validator->validate('token', new Recaptcha(secretKey: 'secret'));

    $this->buildViolation('The Captcha is not valid : {{ reason }}.')
      ->setParameter('{{ reason }}', 'Robot')
      ->assertRaised();
  }

  public function testOtherConstraintRejected(): void
  {
    $this->expectException(UnexpectedTypeException::class);

    $this->validator->validate('token', new NotNull());
  }
}
