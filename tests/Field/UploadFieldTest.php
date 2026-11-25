<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Field;

use Austral\FormBundle\Field\UploadField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

final class UploadFieldTest extends TestCase
{
  public function testDefaults(): void
  {
    $field = UploadField::create('image');

    self::assertSame(FileType::class, $field->getSymfonyType());
    self::assertSame('off', $field->getSymfonyOptions()['attr']['autocomplete']);
    self::assertSame(UploadField::BLOCK_SIZE_DETAIL, $field->getBlockSize());
    self::assertSame('file.error.maxSize', $field->getMaxSizeMessage());
    self::assertSame('file.error.mimeTypes', $field->getMimeTypesMessage());
    self::assertNull($field->getTypeFile());
    self::assertGreaterThan(0, $field->getMaxSizeInBytes());
  }

  public function testMaxSize(): void
  {
    $field = UploadField::create('image', ['max_size' => '2M']);

    self::assertSame('2M', $field->getMaxSize());
    self::assertSame(2000000, $field->getMaxSizeInBytes());
    self::assertSame(1024, $field->setMaxSize('1Ki')->getMaxSizeInBytes());
    self::assertSame(500, $field->setMaxSize(500)->getMaxSize());
  }

  public function testFileConstraint(): void
  {
    $constraints = UploadField::create('image', ['max_size' => '5M'])->getAutoConstraints();

    self::assertCount(1, $constraints);
    self::assertInstanceOf(File::class, $constraints[0]);
    self::assertSame(5000000, $constraints[0]->maxSize);
    self::assertSame([], $constraints[0]->mimeTypes);
    self::assertSame([], $constraints[0]->extensions);
    self::assertSame('file.error.maxSize', $constraints[0]->maxSizeMessage);
    self::assertSame('file.error.mimeTypes', $constraints[0]->mimeTypesMessage);
  }

  public function testFileConstraintWithMimeTypesAndMessages(): void
  {
    $constraints = UploadField::create('image', [
      'mime_types' => ['image/png', 'image/jpeg'],
      'max_size_message' => 'errors.image.size',
      'mime_types_message' => 'errors.image.type',
    ])->getAutoConstraints();

    self::assertInstanceOf(File::class, $constraints[0]);
    self::assertSame(['image/png', 'image/jpeg'], $constraints[0]->mimeTypes);
    self::assertContains('png', $constraints[0]->extensions);
    self::assertContains('jpg', $constraints[0]->extensions);
    self::assertContains('jpeg', $constraints[0]->extensions);
    self::assertSame('errors.image.size', $constraints[0]->maxSizeMessage);
    self::assertSame('errors.image.type', $constraints[0]->mimeTypesMessage);
  }

  public function testImageConstraintWhenASizeIsLimited(): void
  {
    $field = UploadField::create('image', ['image_sizes' => ['min_width' => 800, 'max_height' => 2000]]);
    $constraints = $field->getAutoConstraints();

    self::assertTrue($field->hasImageSizes());
    self::assertCount(2, $constraints);
    self::assertInstanceOf(Image::class, $constraints[1]);
    self::assertSame(800, $constraints[1]->minWidth);
    self::assertNull($constraints[1]->maxWidth);
    self::assertSame(2000, $constraints[1]->maxHeight);
    self::assertFalse(UploadField::create('image')->hasImageSizes());
  }

  /**
   * A listener (entity-file) only fills what the Admin class did not give.
   */
  public function testWhatWasGiven(): void
  {
    $field = UploadField::create('image');

    self::assertFalse($field->hasMaxSize());
    self::assertFalse($field->hasMaxSizeMessage());
    self::assertFalse($field->hasMimeTypesMessage());

    $field->setMaxSize('1M')->setMaxSizeMessage('errors.size')->setMimeTypesMessage('errors.type');

    self::assertTrue($field->hasMaxSize());
    self::assertSame('errors.size', $field->getMaxSizeMessage());
    self::assertSame('errors.type', $field->getMimeTypesMessage());
    self::assertSame('file.error.maxSize', $field->setMaxSizeMessage(null)->getMaxSizeMessage());
  }

  public function testMimeTypesAndExtensions(): void
  {
    $field = UploadField::create('image')->setMimeTypes(['image/png', 'image/jpeg', 'pdf', 'image/png']);

    self::assertSame(['image/png', 'image/jpeg', 'pdf', 'image/png'], $field->getMimeTypes());
    self::assertSame(['png', 'jpeg', 'pdf'], $field->getExtensions());
  }

  public function testImageSizes(): void
  {
    $field = UploadField::create('image')->setImageSizes(['min_width' => 800]);

    self::assertSame(['min_width' => 800, 'max_width' => null, 'min_height' => null, 'max_height' => null], $field->getImageSizes());
  }

  public function testCropper(): void
  {
    $field = UploadField::create('image')->setCropper([
      'wide' => ['key' => 'wide', 'name' => 'Wide', 'ratio' => '16/9'],
      'square' => ['key' => 'square', 'name' => 'Square', 'ratio' => 1, 'picto' => 'austral-picto-square'],
      'free' => ['key' => 'free', 'name' => 'Free', 'ratio' => null],
    ]);
    $cropper = $field->getCropper();

    self::assertEqualsWithDelta(16 / 9, $cropper['wide']['aspect_ratio'], 0.0001);
    self::assertSame(1.0, $cropper['square']['aspect_ratio']);
    self::assertSame('austral-picto-square', $cropper['square']['picto']);
    self::assertNull($cropper['free']['aspect_ratio']);
  }

  public function testCropperNeedsKeyNameAndRatio(): void
  {
    $this->expectException(MissingOptionsException::class);

    UploadField::create('image', ['cropper' => [['key' => 'wide']]]);
  }

  public function testFileParameters(): void
  {
    $field = UploadField::create('image')->setTypeFile('image')->setFileParameters([
      'reel_filename' => 'photo.png',
      'path' => ['view' => '/uploads/photo.png'],
      'infos' => ['mime_type' => 'image/png', 'image_dimension' => ['width' => 800]],
    ]);
    $file = $field->getFileParameters();

    self::assertSame('image', $field->getTypeFile());
    self::assertSame('photo.png', $file['reel_filename']);
    self::assertSame(['view' => '/uploads/photo.png', 'original' => null, 'download' => null, 'absolute' => null], $file['path']);
    self::assertSame('image/png', $file['infos']['mime_type']);
    self::assertSame(['width' => 800, 'height' => null], $file['infos']['image_dimension']);
  }

  public function testWidget(): void
  {
    $widget = UploadField::create('image', ['max_size' => 1000, 'mime_types' => ['image/png'], 'block_size' => UploadField::BLOCK_SIZE_LIGHT, 'cropper_field_key' => 'imageCropper'])->getPresentation()['widget'];

    self::assertSame('little', $widget['block_size']);
    self::assertSame(1000, $widget['max_size']);
    self::assertSame(['png'], $widget['extensions']);
    self::assertSame('imageCropper', $widget['cropper_field_key']);
    self::assertSame([], $widget['cropper']);
    self::assertArrayHasKey('file', $widget);
  }

  public function testUnknownBlockSizeRejected(): void
  {
    $this->expectException(InvalidOptionsException::class);

    UploadField::create('image', ['block_size' => 'huge']);
  }
}
