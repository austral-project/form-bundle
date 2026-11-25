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
 * Entry of the collection tests.
 */
class GalleryItem
{
  public ?string $type = null;

  public ?string $label = null;

  public ?int $position = null;

  /**
   * @param string|null $label
   * @param int|null    $position
   * @param string|null $type
   */
  public function __construct(?string $label = null, ?int $position = null, ?string $type = null)
  {
    $this->label = $label;
    $this->position = $position;
    $this->type = $type;
  }
}
