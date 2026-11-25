<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Fixtures;

/**
 * Data class of the integration tests of the password, date, time, color and upload fields.
 */
class Account
{
  public ?string $plainPassword = null;

  public ?\DateTimeInterface $birthday = null;

  public ?\DateTimeInterface $openingAt = null;

  public ?string $color = null;

  public ?string $content = null;

  public mixed $avatar = null;
}
