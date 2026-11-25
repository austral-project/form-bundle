<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Validator;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Austral Recaptcha validator.
 *
 * Asks Google to verify the token (TLS verified, short timeout). The value is refused whenever it cannot be
 * verified: no secret key, no token, Google unreachable or an unreadable answer, a failure, another action
 * (v3) or a score under the limit (v3). The reasons are the error codes of Google, translated
 * (recaptcha.error.<code>, domain validators) when a translator is given.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class RecaptchaValidator extends ConstraintValidator
{
  public const string VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

  /**
   * @param HttpClientInterface      $httpClient
   * @param RequestStack             $requestStack Gives the IP of the client
   * @param TranslatorInterface|null $translator
   */
  public function __construct(
    private readonly HttpClientInterface $httpClient,
    private readonly RequestStack $requestStack,
    private readonly ?TranslatorInterface $translator = null,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void
  {
    if (!$constraint instanceof Recaptcha)
    {
      throw new UnexpectedTypeException($constraint, Recaptcha::class);
    }
    if (null === $constraint->secretKey || '' === $constraint->secretKey)
    {
      $this->refuse($constraint, ['missing-input-secret']);

      return;
    }
    if (!\is_string($value) || '' === $value)
    {
      $this->refuse($constraint, ['missing-input-response']);

      return;
    }
    $answer = $this->verify($constraint->secretKey, $value);
    if (null === $answer)
    {
      $this->refuse($constraint, ['unavailable']);

      return;
    }
    if (true !== ($answer['success'] ?? null))
    {
      $codes = array_values(array_filter((array) ($answer['error-codes'] ?? []), 'is_string'));
      $this->refuse($constraint, $codes ?: ['invalid-input-response']);

      return;
    }
    if (null !== $constraint->action && isset($answer['action']) && $answer['action'] !== $constraint->action)
    {
      $this->refuse($constraint, ['action-mismatch']);

      return;
    }
    if (isset($answer['score']) && is_numeric($answer['score']) && (float) $answer['score'] < $constraint->scoreLimit)
    {
      $this->refuse($constraint, [$constraint->scoreMessage]);
    }
  }

  /**
   * Answer of Google, null when it cannot be read.
   *
   * @param string $secretKey
   * @param string $token
   *
   * @return array<string, mixed>|null
   */
  private function verify(string $secretKey, string $token): ?array
  {
    $body = ['secret' => $secretKey, 'response' => $token];
    if ($ip = $this->requestStack->getCurrentRequest()?->getClientIp())
    {
      $body['remoteip'] = $ip;
    }
    try
    {
      $response = $this->httpClient->request('POST', self::VERIFY_URL, ['body' => $body, 'timeout' => 5, 'max_duration' => 10]);
      if (200 !== $response->getStatusCode())
      {
        return null;
      }

      return $response->toArray();
    }
    catch (ExceptionInterface)
    {
      return null;
    }
  }

  /**
   * @param Recaptcha    $constraint
   * @param list<string> $codes
   *
   * @return void
   */
  private function refuse(Recaptcha $constraint, array $codes): void
  {
    $reasons = array_map(fn (string $code): string => $this->translator?->trans("recaptcha.error.{$code}", [], 'validators') ?? "recaptcha.error.{$code}", $codes);
    $this->context->buildViolation($constraint->message)
      ->setParameter('{{ reason }}', implode(', ', $reasons))
      ->addViolation();
  }
}
