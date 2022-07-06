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
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $options = array()): UploadField
  {
    return new self($fieldname, $options);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    $maxUpload = AustralTools::unHumanizeSize(ini_get('upload_max_filesize'));
    $postMaxSize = AustralTools::unHumanizeSize(ini_get('post_max_size'));
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
      $this->options["templatePath"] = "upload-file.html.twig";
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
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('upload-file-parameters', function (OptionsResolver $resolverChild) {
      $resolverChild->setDefaults(array(
          "maxSize"             =>  null,
          "imageSizes"          =>  array(),
          "mimeTypes"           =>  array(),
          "mimeTypesMessage"    =>  null,
          "maxSizeMessage"      =>  null
        )
      );
      $resolverChild->setAllowedTypes('maxSize', array('string', "null"));
      $resolverChild->setAllowedTypes('imageSizes', array('array', "null"));
      $resolverChild->setAllowedTypes('mimeTypes', array('array', "null"));
      $resolverChild->setAllowedTypes('mimeTypesMessage', array('string', "null"));
      $resolverChild->setAllowedTypes('maxSizeMessage', array('string', "null"));
      $resolverChild->setDefault('file', function (OptionsResolver $resolverSubChild) {
        $this->resolverConfigureFile($resolverSubChild);
      });
    });
    $resolver->setDefault('blockSize', self::DETAIL);
    $resolver->setDefault('cropper', array());
    $resolver->setDefault('cropperFieldKey', null);
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function resolverConfigureImageSizes(OptionsResolver $resolver)
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
  protected function resolverConfigureFile(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
        "reelFilename"  =>  null,
      )
    );
    $resolver->setAllowedTypes('reelFilename', array('string', "null"));
    $resolver->setDefault('path', function (OptionsResolver $resolverSubChild) {
      $resolverSubChild->setDefaults(array(
          "view"          =>  null,
          "download"      =>  null,
          "absolute"      =>  null,
        )
      );
      $resolverSubChild->setAllowedTypes('view', array('string', "null"));
      $resolverSubChild->setAllowedTypes('download', array('string', "null"));
      $resolverSubChild->setAllowedTypes('absolute', array('string', "null"));
    });
    $resolver->setDefault('infos', function (OptionsResolver $resolverSubChild) {
      $resolverSubChild->setDefaults(array(
          "mimeType"        =>  null,
          "extension"       =>  null,
          "size"            =>  null,
          "sizeHuman"       =>  null,
          "imageSize"       =>  null,
        )
      );
      $resolverSubChild->setAllowedTypes('mimeType', array('string', "null"));
      $resolverSubChild->setAllowedTypes('size', array('int', "float", "null"));
    });
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function resolverConfigureCropper(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
        "picto"       =>  null,
      )
    );
    $resolver->define("name")->required();
    $resolver->define("ratio")->required();
    $resolver->setAllowedTypes('name', array('string'));
    $resolver->setAllowedTypes('picto', array('string', "null"));
  }

  /**
   * @param array $fileParameters
   */
  public function setFileParameters(array $fileParameters)
  {
    $resolver = new OptionsResolver();
    $this->resolverConfigureFile($resolver);
    $this->options['upload-file-parameters']['file'] = $resolver->resolve($fileParameters);
  }

  /**
   * @return array
   */
  public function getFileParameters(): array
  {
    return $this->options['upload-file-parameters']['file'];
  }

  /**
   * @param array $cropper
   */
  public function setCropper(array $cropper)
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
  }

  /**
   * @return array
   */
  public function getCropper(): array
  {
    return $this->options['cropper'];
  }

  /**
   * @return array|mixed
   */
  public function getUploadFileParameters()
  {
    $mimetypesString = implode(" ", $this->options['upload-file-parameters']['mimeTypes']);
    $this->options['upload-file-parameters']["extensions"] = preg_replace('/(\w{0,}\/)/', ".", $mimetypesString);
    return $this->options['upload-file-parameters'];
  }

  /**
   * @param false $humanize
   *
   * @return float|string
   */
  public function getMaxSize(bool $humanize = false, $default = true)
  {
    $value = $this->options['upload-file-parameters']['maxSize'];
    if(!$value && $default)
    {
      $value = $this->maxSizeDefault;
    }
    return $humanize ? AustralTools::unHumanizeSize($value) : $value;
  }

  /**
   * @var string $maxSize
   * @return UploadField
   */
  public function setMaxSize(string $maxSize): UploadField
  {
    $this->options['upload-file-parameters']['maxSize'] = $maxSize;
    return $this;
  }

  /**
   * @return array
   */
  public function getImageSizes(): array
  {
    return $this->options['upload-file-parameters']['imageSizes'];
  }

  /**
   * @param array $imageSizes
   *
   * @return UploadField
   */
  public function setImageSizes(array $imageSizes): UploadField
  {
    $resolver = new OptionsResolver();
    $this->resolverConfigureImageSizes($resolver);
    $this->options['upload-file-parameters']["imageSizes"] = $resolver->resolve($imageSizes);
    return $this;
  }

  /**
   * @return array
   */
  public function getMimeTypes(): array
  {
    return $this->options['upload-file-parameters']['mimeTypes'];
  }

  /**
   * @var array $mimeTypes
   * @return UploadField
   */
  public function setMimeTypes(array $mimeTypes): UploadField
  {
    $this->options['upload-file-parameters']['mimeTypes'] = $mimeTypes;
    return $this;
  }

  /**
   * @return array
   */
  public function extensions(): array
  {
    $extensions = array();
    foreach ($this->getMimeTypes() as $mimeType)
    {
      if(strpos($mimeType, "/") !== false)
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
   * @var string $maxSizeMessage
   * @return UploadField
   */
  public function setMaxSizeMessage(string $maxSizeMessage): UploadField
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
   * @var string $mimeTypesMessage
   * @return UploadField
   */
  public function setMimeTypesMessage(string $mimeTypesMessage): UploadField
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
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions['mapped'] = true;
    $fieldOptions['attr']["autocomplete"] = "off";
    return $fieldOptions;
  }

}