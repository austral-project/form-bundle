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

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * Austral Recaptcha constraint.
 *
 * Carries what the validator needs (secret key, action, score limit): it is set by RecaptchaField, so the
 * validator no longer looks for the field in a registry of mappers.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Recaptcha extends Constraint
{
  /**
   * @var string
   */
  public string $message = 'The Captcha is not valid : {{ reason }}.';

  /**
   * @param string|null       $secretKey    Without a secret key, the value is refused
   * @param string|null       $action       reCAPTCHA v3: the action of the token must be this one
   * @param float             $scoreLimit   reCAPTCHA v3: a lower score is refused
   * @param string            $scoreMessage Reason of a too low score (translation key recaptcha.error.<reason>)
   * @param string|null       $message
   * @param list<string>|null $groups
   * @param mixed             $payload
   */
  #[HasNamedArguments]
  public function __construct(
    public ?string $secretKey = null,
    public ?string $action = null,
    public float $scoreLimit = 0.7,
    public string $scoreMessage = 'bot-detected',
    ?string $message = null,
    ?array $groups = null,
    mixed $payload = null,
  ) {
    parent::__construct(null, $groups, $payload);
    $this->message = $message ?? $this->message;
  }
}
