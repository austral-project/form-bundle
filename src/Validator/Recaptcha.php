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
 * @Attribute
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Recaptcha extends Constraint
{
  public string $message = 'The Captcha is not valid : {{ reason }}.';

  /**
   * Recaptcha constructor
   *
   * @param mixed|null $options
   * @param string|null $message
   * @param string[]|null $groups
   * @param mixed|null $payload
   */
  #[HasNamedArguments]
  public function __construct(
    mixed $options = null,
    ?string $message = null,
    ?array $groups = null,
    mixed $payload = null)
  {
    parent::__construct($options ?? [], $groups, $payload);
    $this->message = $message ?? $this->message;
  }
}