<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Field;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * Austral Upload Field.
 *
 * FileType with the parameters of the upload (size, MIME types, image sizes, croppers) and the description of
 * the current file ("file": names, paths, infos), filled by entity-file-bundle. Everything is exposed as
 * form.vars.austral.widget. With "auto_constraints", the field adds File(maxSize).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class UploadField extends Field
{
  public const string BLOCK_SIZE_DETAIL = 'detail';
  public const string BLOCK_SIZE_LIGHT = 'little';

  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = FileType::class;

  /**
   * @param string               $name
   * @param array<string, mixed> $options
   *
   * @return self
   */
  public static function create(string $name, array $options = []): self
  {
    return new self($name, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults([
      'max_size' => null,
      'max_size_message' => 'file.error.maxSize',
      'mime_types' => [],
      'mime_types_message' => 'file.error.mimeTypes',
      'block_size' => self::BLOCK_SIZE_DETAIL,
      'type_file' => null,
      'cropper' => [],
      'cropper_field_key' => null,
    ]);
    $resolver->setAllowedTypes('max_size', ['null', 'int', 'string'])
      ->setAllowedTypes('max_size_message', 'string')
      ->setAllowedTypes('mime_types', 'string[]')
      ->setAllowedTypes('mime_types_message', 'string')
      ->setAllowedValues('block_size', [self::BLOCK_SIZE_DETAIL, self::BLOCK_SIZE_LIGHT])
      ->setAllowedTypes('type_file', ['null', 'string'])
      ->setAllowedTypes('cropper', 'array')
      ->setAllowedTypes('cropper_field_key', ['null', 'string']);
    $resolver->setNormalizer('cropper', static fn ($options, array $croppers): array => array_map(self::resolveCropper(...), $croppers));
    $resolver->setOptions('image_sizes', static function (OptionsResolver $sizes): void {
      foreach (['min_width', 'max_width', 'min_height', 'max_height'] as $key)
      {
        $sizes->setDefault($key, null)->setAllowedTypes($key, ['null', 'int']);
      }
    });
    $resolver->setOptions('file', static function (OptionsResolver $file): void {
      $file->setDefault('reel_filename', null)->setAllowedTypes('reel_filename', ['null', 'string']);
      $file->setOptions('path', static function (OptionsResolver $path): void {
        foreach (['view', 'original', 'download', 'absolute'] as $key)
        {
          $path->setDefault($key, null)->setAllowedTypes($key, ['null', 'string']);
        }
      });
      $file->setOptions('infos', static function (OptionsResolver $infos): void {
        $infos->setDefaults([
          'mime_type' => null,
          'extension' => null,
          'size' => null,
          'size_human' => null,
          'image_size' => null,
          'aspect_ratio' => null,
        ])
          ->setAllowedTypes('mime_type', ['null', 'string'])
          ->setAllowedTypes('extension', ['null', 'string'])
          ->setAllowedTypes('size', ['null', 'int', 'float'])
          ->setAllowedTypes('size_human', ['null', 'string'])
          ->setAllowedTypes('image_size', ['null', 'string'])
          ->setAllowedTypes('aspect_ratio', ['null', 'int', 'float']);
        $infos->setOptions('image_dimension', static function (OptionsResolver $dimension): void {
          $dimension->setDefaults(['width' => null, 'height' => null])
            ->setAllowedTypes('width', ['null', 'int', 'float'])
            ->setAllowedTypes('height', ['null', 'int', 'float']);
        });
      });
    });
  }

  /**
   * Maximum size as given (int in bytes, or "2M", "500k"...), otherwise the smallest of upload_max_filesize,
   * post_max_size and memory_limit, in bytes.
   *
   * @return int|string
   */
  public function getMaxSize(): int|string
  {
    return $this->options['max_size'] ?? self::serverMaxSize();
  }

  /**
   * @return int
   */
  public function getMaxSizeInBytes(): int
  {
    return (int) (new File(maxSize: $this->getMaxSize()))->maxSize;
  }

  /**
   * @param int|string|null $maxSize
   *
   * @return $this
   */
  public function setMaxSize(int|string|null $maxSize): static
  {
    return $this->setOptions(['max_size' => $maxSize]);
  }

  /**
   * @return list<string>
   */
  public function getMimeTypes(): array
  {
    return $this->options['mime_types'];
  }

  /**
   * @param list<string> $mimeTypes
   *
   * @return $this
   */
  public function setMimeTypes(array $mimeTypes): static
  {
    return $this->setOptions(['mime_types' => $mimeTypes]);
  }

  /**
   * Extensions of the MIME types: image/png => png, a value without "/" is kept as it is.
   *
   * @return list<string>
   */
  public function getExtensions(): array
  {
    $extensions = [];
    foreach ($this->options['mime_types'] as $mimeType)
    {
      $extension = str_contains($mimeType, '/') ? substr($mimeType, strpos($mimeType, '/') + 1) : $mimeType;
      $extensions[$extension] = $extension;
    }

    return array_values($extensions);
  }

  /**
   * @return string
   */
  public function getMaxSizeMessage(): string
  {
    return $this->options['max_size_message'];
  }

  /**
   * @return string
   */
  public function getMimeTypesMessage(): string
  {
    return $this->options['mime_types_message'];
  }

  /**
   * @return array{min_width: ?int, max_width: ?int, min_height: ?int, max_height: ?int}
   */
  public function getImageSizes(): array
  {
    return $this->options['image_sizes'];
  }

  /**
   * @param array<string, int|null> $imageSizes
   *
   * @return $this
   */
  public function setImageSizes(array $imageSizes): static
  {
    return $this->setOptions(['image_sizes' => $imageSizes]);
  }

  /**
   * @return string
   */
  public function getBlockSize(): string
  {
    return $this->options['block_size'];
  }

  /**
   * @return string|null
   */
  public function getTypeFile(): ?string
  {
    return $this->options['type_file'];
  }

  /**
   * @param string|null $typeFile
   *
   * @return $this
   */
  public function setTypeFile(?string $typeFile): static
  {
    return $this->setOptions(['type_file' => $typeFile]);
  }

  /**
   * @return array<array-key, array{key: string, name: string, ratio: string|int|float|null, picto: ?string, aspect_ratio: ?float}>
   */
  public function getCropper(): array
  {
    return $this->options['cropper'];
  }

  /**
   * @param array<array-key, array<string, mixed>> $cropper
   *
   * @return $this
   */
  public function setCropper(array $cropper): static
  {
    return $this->setOptions(['cropper' => $cropper]);
  }

  /**
   * Description of the current file.
   *
   * @return array<string, mixed>
   */
  public function getFileParameters(): array
  {
    return $this->options['file'];
  }

  /**
   * @param array<string, mixed> $file
   *
   * @return $this
   */
  public function setFileParameters(array $file): static
  {
    return $this->setOptions(['file' => $file]);
  }

  /**
   * {@inheritdoc}
   */
  public function getAutoConstraints(): array
  {
    return [new File(maxSize: $this->getMaxSize())];
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $options['attr'] += ['autocomplete' => 'off'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getWidget(): array
  {
    return [
      'block_size' => $this->options['block_size'],
      'type_file' => $this->options['type_file'],
      'max_size' => $this->getMaxSizeInBytes(),
      'max_size_message' => $this->options['max_size_message'],
      'mime_types' => $this->options['mime_types'],
      'mime_types_message' => $this->options['mime_types_message'],
      'extensions' => $this->getExtensions(),
      'image_sizes' => $this->options['image_sizes'],
      'cropper' => $this->options['cropper'],
      'cropper_field_key' => $this->options['cropper_field_key'],
      'file' => $this->options['file'],
    ];
  }

  /**
   * A cropper: key, name and ratio required ("16/9" or a number), picto optional; aspect_ratio is computed.
   *
   * @param array<string, mixed> $cropper
   *
   * @return array{key: string, name: string, ratio: string|int|float|null, picto: ?string, aspect_ratio: ?float}
   */
  private static function resolveCropper(array $cropper): array
  {
    $resolver = new OptionsResolver();
    $resolver->setRequired(['key', 'name', 'ratio'])
      ->setDefault('picto', null)
      ->setAllowedTypes('key', 'string')
      ->setAllowedTypes('name', 'string')
      ->setAllowedTypes('ratio', ['null', 'string', 'int', 'float'])
      ->setAllowedTypes('picto', ['null', 'string']);
    /** @var array{key: string, name: string, ratio: string|int|float|null, picto: ?string} $cropper */
    $cropper = $resolver->resolve($cropper);
    $ratio = $cropper['ratio'];
    if (\is_string($ratio) && str_contains($ratio, '/'))
    {
      [$width, $height] = explode('/', $ratio, 2);
      $aspectRatio = (float) $height ? (float) $width / (float) $height : null;
    }
    else
    {
      $aspectRatio = null === $ratio || '' === $ratio ? null : (float) $ratio;
    }

    return $cropper + ['aspect_ratio' => $aspectRatio];
  }

  /**
   * Smallest of upload_max_filesize, post_max_size and memory_limit, in bytes (php.ini units: K, M, G = 1024).
   * A limit that is not set, or -1, is ignored.
   *
   * @return int
   */
  private static function serverMaxSize(): int
  {
    $sizes = [];
    foreach (['upload_max_filesize', 'post_max_size', 'memory_limit'] as $directive)
    {
      if (($bytes = self::iniBytes((string) ini_get($directive))) > 0)
      {
        $sizes[] = $bytes;
      }
    }

    return $sizes ? min($sizes) : 2 * 1024 * 1024;
  }

  /**
   * @param string $value
   *
   * @return int
   */
  private static function iniBytes(string $value): int
  {
    if (!preg_match('/^\s*(-?\d+)\s*([kmg]?)\s*$/i', $value, $matches))
    {
      return 0;
    }
    $exponent = ['' => 0, 'k' => 1, 'm' => 2, 'g' => 3][strtolower($matches[2])];

    return (int) $matches[1] * (1024 ** $exponent);
  }
}
