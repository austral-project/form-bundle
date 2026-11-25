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
 * Data class of the field integration tests.
 */
class Product
{
  public ?string $name = null;

  public ?string $description = null;

  public int|float|null $price = null;

  public ?int $stock = null;

  public bool $enabled = false;

  public ?string $reference = null;
}
