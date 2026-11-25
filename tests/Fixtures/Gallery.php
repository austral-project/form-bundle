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
 * Data class of the collection tests: a gallery and its items.
 */
class Gallery
{
  public ?string $title = null;

  /**
   * @var array<int, GalleryItem> Keys are kept by the collection on submit (removed entries leave holes)
   */
  public array $items = [];

  /**
   * @param string $type
   *
   * @return list<GalleryItem>
   */
  public function getItemsByType(string $type): array
  {
    return array_values(array_filter($this->items, static fn (GalleryItem $item): bool => $item->type === $type));
  }

  /**
   * Replaces the items of a type, keeps the others.
   *
   * @param string                  $type
   * @param array<int, GalleryItem> $items
   *
   * @return void
   */
  public function setItemsByType(string $type, array $items): void
  {
    foreach ($items as $item)
    {
      $item->type = $type;
    }
    $this->items = array_merge(array_values(array_filter($this->items, static fn (GalleryItem $item): bool => $item->type !== $type)), array_values($items));
  }
}
