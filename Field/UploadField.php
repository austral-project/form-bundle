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

use Austral\FormBundle\Field\Base\Field;

use Austral\ToolsBundle\AustralTools;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field Upload File Input.
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class UploadField extends Field
{

  const DETAIL = "detail";
  const LIGHT = "little";

  /**
   * @var string
   */
  protected string $maxSizeDefault;
  
  /**
   * @var string|null
   */
  protected ?string $typeFile = null;


  /**
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, array $options = []): self
  {
    return new self($fieldname, $options);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, array $options = [])
  {
    $maxUpload = AustralTools::unHumanizeSize(ini_get('upload_max_filesize') === false ? null : ini_get('upload_max_filesize'));
    $postMaxSize = AustralTools::unHumanizeSize(ini_get('post_max_size') === false ? null : ini_get('post_max_size'));
    $memoryLimit = AustralTools::unHumanizeSize(ini_get('memory_limit'));
    $maxUploadDefault = min($maxUpload, $postMaxSize, $memoryLimit);
    $this->maxSizeDefault = AustralTools::humanizeSize($maxUploadDefault, "Ko", 1, false)."k";

    parent::__construct($fieldname, $options);

    $this->symfonyFormType = FileType::class;

    $cropperResolver = new OptionsResolver();
    $this->resolverConfigureCropper($cropperResolver);
    foreach($this->options['cropper'] as $key => $values)
    {
      $this->options['cropper'][$key] = $cropperResolver->resolve($values);
    }

    if($this->isDefaultTemplate)
    {
      $this->options["template"]["path"] = "upload-file.html.twig";
    }
  }
  
  /**
   * @param string $typeFile
   *
   * @return $this
   */
  public function setTypeFile(string $typeFile): UploadField
  {
    $this->typeFile = $typeFile;
    return $this;
  }
  
  /**
   * @return string|null
   */
  public function getTypeFile(): ?string
  {
    return $this->typeFile;
  }

  /**
   * @inheritDoc
   */
  public function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('upload-file-parameters', function (OptionsResolver $resolverChild) {
      $this->resolverConfigureUploadFileParameters($resolverChild);
    });
    $resolver->setDefault('blockSize', self::DETAIL);
    $resolver->setDefault('cropper', []);
    $resolver->setDefault('cropperFieldKey', null);
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function resolverConfigureUploadFileParameters(OptionsResolver $resolver): void
  {
    $resolver->setDefaults(array(
        "maxSize"             =>  null,
        "imageSizes"          =>  [],
        "mimeTypes"           =>  [],
        "mimeTypesMessage"    =>  null,
        "maxSizeMessage"      =>  null,
        "extensions"          =>  null
      )
    );
    $resolver->setAllowedTypes('maxSize', array('string', 'int', "null"));
    $resolver->setAllowedTypes('imageSizes', array('array', "null"));
    $resolver->setAllowedTypes('mimeTypes', array('array', "null"));
    $resolver->setAllowedTypes('mimeTypesMessage', array('string', "null"));
    $resolver->setAllowedTypes('maxSizeMessage', array('string', "null"));
    $resolver->setAllowedTypes('extensions', array('string', "null"));
    $resolver->setDefault('file', function (OptionsResolver $resolverSubChild) {
      $this->resolverConfigureFile($resolverSubChild);
    });
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function resolverConfigureImageSizes(OptionsResolver $resolver): void
  {
    $resolver->setDefaults(array(
        "minWidth"    =>  null,
        "maxWidth"    =>  null,
        "minHeight"   =>  null,
        "maxHeight"   =>  null,
      )
    );
    $resolver->setAllowedTypes('minWidth', array('int', "null"));
    $resolver->setAllowedTypes('maxWidth', array('int', "null"));
    $resolver->setAllowedTypes('minHeight', array('int', "null"));
    $resolver->setAllowedTypes('maxHeight', array('int', "null"));
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function resolverConfigureInfos(OptionsResolver $resolver): void
  {
    $resolver->setDefaults(array(
        "mimeType"        =>  null,
        "extension"       =>  null,
        "size"            =>  null,
        "sizeHuman"       =>  null,
        "imageSize"       =>  null,
        "aspectRatio"     =>  null,
      )
    );
    $resolver->setDefault('imageDimension', function (OptionsResolver $resolverSubChild) {
      $resolverSubChild->setDefault("width", null)->setAllowedTypes('width', array('int', 'float', "null"));
      $resolverSubChild->setDefault("height", null)->setAllowedTypes('height', array('int', 'float', "null"));
    });
    $resolver->setAllowedTypes('mimeType', array('string', "null"));
    $resolver->setAllowedTypes('extension', array('string', "null"));
    $resolver->setAllowedTypes('size', array('int', 'float', "null"));
    $resolver->setAllowedTypes('sizeHuman', array('string', "null"));
    $resolver->setAllowedTypes('imageSize', array('string', "null"));
    $resolver->setAllowedTypes('aspectRatio', array('int', 'float', "null"));
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function resolverConfigureFile(OptionsResolver $resolver): void
  {
    $resolver->setDefaults(array(
        "reelFilename"  =>  null,
      )
    );
    $resolver->setAllowedTypes('reelFilename', array('string', "null"));
    $resolver->setDefault('path', function (OptionsResolver $resolverSubChild) {
      $resolverSubChild->setDefaults(array(
          "view"          =>  null,
          "original"      =>  null,
          "download"      =>  null,
          "absolute"      =>  null,
        )
      );
      $resolverSubChild->setAllowedTypes('view', array('string', "null"));
      $resolverSubChild->setAllowedTypes('original', array('string', "null"));
      $resolverSubChild->setAllowedTypes('download', array('string', "null"));
      $resolverSubChild->setAllowedTypes('absolute', array('string', "null"));
    });
    $resolver->setDefault('infos', function (OptionsResolver $resolverSubChild) {
      $this->resolverConfigureInfos($resolverSubChild);
    });

  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function resolverConfigureCropper(OptionsResolver $resolver): void
  {
    $resolver->setDefaults(array(
        "picto"       =>  null,
      )
    );
    $resolver->define("key")->required();
    $resolver->define("name")->required();
    $resolver->define("ratio")->required();
    $resolver->setAllowedTypes('name', array('string'));
    $resolver->setAllowedTypes('key', array('string'));
    $resolver->setAllowedTypes('picto', array('string', "null"));
  }

  /**
   * @param array<string, mixed> $fileParameters
   */
  public function setFileParameters(array $fileParameters): void
  {
    $resolver = new OptionsResolver();
    $this->resolverConfigureFile($resolver);
    $this->options['upload-file-parameters']['file'] = $resolver->resolve($fileParameters);
  }

  /**
   * @return array<string, mixed>
   */
  public function getFileParameters(): array
  {
    return $this->options['upload-file-parameters']['file'];
  }

  /**
   * @param array<string, mixed> $cropper
   * @return $this
   */
  public function setCropper(array $cropper): self
  {
    $resolver = new OptionsResolver();
    $this->resolverConfigureCropper($resolver);
    foreach($cropper as $key => $values)
    {
      $values = $resolver->resolve($values);
      $values["aspectRatio"] = null;
      if($values["ratio"])
      {
        if(strpos($values["ratio"], "/"))
        {
          list($x, $y) = explode("/", $values['ratio']);
          $values["aspectRatio"] = (float) $x / (float) $y;
        }
        else
        {
          $values["aspectRatio"] = $values["ratio"];
        }
      }
      $this->options['cropper'][$key] = $values;
    }
    return $this;
  }

  /**
   * @return array<string, mixed>
   */
  public function getCropper(): array
  {
    return $this->options['cropper'];
  }

  /**
   * @return array<string, mixed>
   */
  public function getUploadFileParameters(): array
  {
    $mimetypesString = implode(" ", $this->options['upload-file-parameters']['mimeTypes']);
    $this->options['upload-file-parameters']["extensions"] = preg_replace('/(\w{0,}\/)/', ".", $mimetypesString);
    return $this->options['upload-file-parameters'];
  }

  /**
   * setUploadFileParameters
   *
   * @param array<string, mixed> $uploadFileParameters
   * @return $this
   */
  public function setUploadFileParameters(array $uploadFileParameters): self
  {
    $resolver = new OptionsResolver();
    $this->resolverConfigureUploadFileParameters($resolver);
    $this->options['upload-file-parameters'] = $resolver->resolve($uploadFileParameters);
    return $this;
  }

  /**
   * @param bool $humanize
   * @param bool $useDefault
   * @return positive-int|string
   */
  public function getMaxSize(bool $humanize = false, bool $useDefault = true): int|string
  {
    $value = $this->options['upload-file-parameters']['maxSize'];
    if(!$value && $useDefault)
    {
      $value = $this->maxSizeDefault;
    }
    $maxSize = $humanize ? AustralTools::unHumanizeSize($value) : $value;
    return is_int($maxSize) ? (max($maxSize, 1)) : $maxSize;
  }

  /**
   * @param positive-int|string $maxSize
   * @return $this
   */
  public function setMaxSize(int|string $maxSize): self
  {
    $this->options['upload-file-parameters']['maxSize'] = $maxSize;
    return $this;
  }

  /**
   * @return array<string, mixed>
   */
  public function getImageSizes(): array
  {
    return $this->options['upload-file-parameters']['imageSizes'];
  }

  /**
   * @param array{minWidth: int|null, maxWidth: int|null, minHeight: int|null, maxHeight: int|null} $imageSizes
   *
   * @return $this
   */
  public function setImageSizes(array $imageSizes): self
  {
    $resolver = new OptionsResolver();
    $this->resolverConfigureImageSizes($resolver);
    $this->options['upload-file-parameters']["imageSizes"] = $resolver->resolve($imageSizes);
    return $this;
  }

  /**
   * @return array<int, string>
   */
  public function getMimeTypes(): array
  {
    return $this->options['upload-file-parameters']['mimeTypes'];
  }

  /**
   * @param array<int, string> $mimeTypes
   * @return $this
   */
  public function setMimeTypes(array $mimeTypes): self
  {
    $this->options['upload-file-parameters']['mimeTypes'] = $mimeTypes;
    return $this;
  }

  /**
   * @return array<string, string>
   */
  public function extensions(): array
  {
    $extensions = [];
    foreach ($this->getMimeTypes() as $mimeType)
    {
      if(str_contains($mimeType, "/"))
      {
        list($mime, $extension) = explode("/", $mimeType);
        $extensions[$extension] = $extension;
      }
      else
      {
        $extensions[$mimeType] = $mimeType;
      }
    }
    return $extensions;
  }

  /**
   * @param bool $default
   *
   * @return string|null
   */
  public function getMaxSizeMessage(bool $default = true): ?string
  {
    return $this->options['upload-file-parameters']['maxSizeMessage'] ? : ($default ? "file.error.maxSize" : null);
  }

  /**
   * @param string $maxSizeMessage
   * @return $this
   */
  public function setMaxSizeMessage(string $maxSizeMessage): self
  {
    $this->options['upload-file-parameters']['maxSizeMessage'] = $maxSizeMessage;
    return $this;
  }

  /**
   * @param bool $default
   *
   * @return string|null
   */
  public function getMimeTypesMessage(bool $default = true): ?string
  {
    return $this->options['upload-file-parameters']['mimeTypesMessage'] ? : ($default ? "file.error.mimeTypes" : null);
  }

  /**
   * @param string $mimeTypesMessage
   * @return $this
   */
  public function setMimeTypesMessage(string $mimeTypesMessage): self
  {
    $this->options['upload-file-parameters']['mimeTypesMessage'] = $mimeTypesMessage;
    return $this;
  }

  /**
   * @return string
   */
  public function getSize(): string
  {
    return $this->options["blockSize"] = $this->options["blockSize"]  ? : self::DETAIL;;
  }

  /**
   * @inheritDoc
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions['mapped'] = true;
    $fieldOptions['attr']["autocomplete"] = "off";
    return $fieldOptions;
  }

}