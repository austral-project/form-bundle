<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Handler;

use Symfony\Component\Form\FormInterface;

/**
 * Austral Form Result: what FormHandler::process() did (replaces the form status of the 3.x FormMapper).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class FormResult
{
  public const string STATUS_NOT_SUBMITTED = 'not_submitted';
  public const string STATUS_INVALID = 'error';
  public const string STATUS_SAVED = 'success';

  /**
   * @param FormInterface<mixed> $form
   * @param string               $status One of the STATUS_* constants
   */
  public function __construct(private readonly FormInterface $form, private readonly string $status)
  {
  }

  /**
   * @return FormInterface<mixed>
   */
  public function getForm(): FormInterface
  {
    return $this->form;
  }

  /**
   * @return string
   */
  public function getStatus(): string
  {
    return $this->status;
  }

  /**
   * @return bool
   */
  public function isSubmitted(): bool
  {
    return self::STATUS_NOT_SUBMITTED !== $this->status;
  }

  /**
   * @return bool
   */
  public function isSaved(): bool
  {
    return self::STATUS_SAVED === $this->status;
  }
}
