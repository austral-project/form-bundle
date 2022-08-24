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

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class Recaptcha extends Constraint
{
  public $message = 'The Captcha is not valid : {{ reason }}.';

  public function __construct(array $options = null, string $message = null, array $groups = null, $payload = null)
  {
    parent::__construct($options ?? [], $groups, $payload);
    $this->message = $message ?? $this->message;
  }


}