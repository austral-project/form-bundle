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
 * Data class of the form tests (its Doctrine mapping is built by hand in the guesser tests).
 */
class Article
{
  public ?string $title = null;

  public ?string $slug = null;

  public ?string $summary = null;

  public ?string $status = null;

  public ?string $views = null;

  public ?string $rating = null;

  public ?string $publishedAt = null;
}
