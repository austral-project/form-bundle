<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Form\Type;

use Austral\EntityBundle\Entity\Interfaces\FileInterface;
use Austral\EntityFileBundle\File\Mapping\FieldFileMapping;
use Austral\FormBundle\Field\Base\FieldInterface;
use Austral\FormBundle\Field\CollectionEmbedField;
use Austral\FormBundle\Field\MultiField;
use Austral\FormBundle\Field\SelectField;
use Austral\FormBundle\Field\UploadField;
use Austral\FormBundle\Mapper\FormMapper;

use Austral\ToolsBundle\AustralTools;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Austral FormType.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class FormType extends AbstractType implements FormTypeInterface
{
  /**
   * @var AuthorizationCheckerInterface|null
   */
  protected ?AuthorizationCheckerInterface $security;

  /**
   * @var ContainerInterface|null
   */
  protected ?ContainerInterface $container = null;

  /**
   * @var string|null
   */
  protected ?string $class;

  /**
   * @var ?FormMapper
   */
  protected ?FormMapper $formMapper = null;

  /**
   * @var array
   */
  protected array $formMappers = array();

  /**
   * @var bool
   */
  protected bool $isHtmlValidate = false;

  /**
   * @var bool
   */
  protected bool $validateGroups = false;

  /**
   * @var string
   */
  protected string $translationDomain = "";

  /**
   * FormType constructor.
   *
   * @param ?AuthorizationCheckerInterface $security
   */
  public function __construct()
  {
  }

  /**
   * setSecurity
   *
   * @param AuthorizationCheckerInterface|null $security
   * @return $this
   */
  public function setSecurity(?AuthorizationCheckerInterface $security = null): FormType
  {
    $this->security = $security;
    return $this;
  }

  /**
   * setContainer
   *
   * @param ContainerInterface|null $container
   * @return $this
   */
  public function setContainer(ContainerInterface $container = null): FormType
  {
    $this->container = $container;
    return $this;
  }

  /**
   * @return string
   */
  public function getBlockPrefix(): string
  {
    return $this->getFormMapper()->getName();
  }

  /**
   * @param FormMapper $formMapper
   *
   * @return $this
   */
  public function setFormMapper(FormMapper $formMapper): FormType
  {
    if(!$this->formMapper)
    {
      $this->formMapper = $formMapper;
    }
    return $this;
  }

  /**
   * @param string $key
   * @param FormMapper $formMapper
   *
   * @return FormType
   */
  public function addFormMappers(string $key, FormMapper $formMapper): FormType
  {
    $this->formMappers[$key] = $formMapper;
    return $this;
  }

  /**
   * @param FormBuilderInterface|null $builder
   *
   * @return FormMapper
   */
  protected function getFormMapper(FormBuilderInterface $builder = null): ?FormMapper
  {
    if($builder)
    {
      $attribute = $builder->getOption("attr");
      if(array_key_exists("formMapperKey", $attribute))
      {
        if(array_key_exists($attribute["formMapperKey"], $this->formMappers))
        {
          return $this->formMappers[$attribute["formMapperKey"]];
        }
        return AustralTools::getValueByKey($this->formMapper->getAllSubFormMapper(), $attribute["formMapperKey"]);
      }
      else
      {
        if(array_key_exists($builder->getName(), $this->formMappers))
        {
          return $this->formMappers[$builder->getName()];
        }
        elseif(array_key_exists($builder->getName(), $this->formMapper->getAllSubFormMapper()))
        {
          return $this->formMapper->getAllSubFormMapper()[$builder->getName()];
        }
      }
    }
    return $this->formMapper;
  }

  /**
   * @param string $class
   *
   * @return $this
   */
  public function setClass(string $class): FormType
  {
    $this->class = $class;
    return $this;
  }

  /**
   * @param $role
   *
   * @return bool
   */
  protected function isGranted($role): bool
  {
    return $this->security->isGranted($role);
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    $defaultParameters = array(
      'validation_groups'   =>  $this->formMapper->getOption("validation_groups"),
      "attr"                =>  array(),
      "translation_domain"  =>  $this->formMapper->getOption("translation_domain")
    );
    if(!$this->formMapper->getOption("html5_validate", false))
    {
      $defaultParameters['attr']["novalidate"] = "novalidate";
    }
    if($uniqueFields = $this->formMapper->getUniqueFields())
    {
      foreach($uniqueFields as $uniqueField)
      {
        $defaultParameters["constraints"][] = new UniqueEntity(array("fields"=>$uniqueField, "message" => "form.errors.unique"));
      }
    }
    $resolver->setDefaults($defaultParameters);
  }

  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   *
   * @return void
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $formMapper = $this->getFormMapper($builder);
    foreach($formMapper->allFields() as $field)
    {
      $this->addFieldToBuildForm($builder, $field, $formMapper);
      $builder->addEventListener(
        FormEvents::PRE_SUBMIT,
        function(FormEvent $event) use($field, $formMapper)
        {
          $formData = $event->getData() ?? array();
          if(array_key_exists($field->getFieldname(), $formData))
          {
            $data = $event->getData()[$field->getFieldname()];
            if($field instanceof SelectField && $field->tagsActivated())
            {
              $choices = array();
              if(is_array($data)){
                foreach($data as $choice){
                  $choices[$choice] = $choice;
                }
              }
              else{
                $choices[$data] = $data;
              }
              $field->setChoices($choices);
              $options = $field->getFieldOptions();
              $options["label"] = $field->getEntitled() ?? false;
              if($contraints = $field->getConstraints())
              {
                $options["constraints"] = $contraints;
              }
              if($field->getSymfonyFormType())
              {
                $event->getForm()->add($field->getFieldname(), $field->getSymfonyFormType(), $options);
              }
            }
          }
        }
      );

      $builder->addEventListener(
        FormEvents::PRE_SET_DATA,
        function(FormEvent $event) use($field, $formMapper) {
          if($data = $event->getData())
          {
            if($field instanceof SelectField && $field->tagsActivated())
            {
              if($choices = $field->getChoicesToTagsActivated($data))
              {
                $field->setChoices($choices);
                $options = $field->getFieldOptions();
                $options["label"] = $field->getEntitled() ?? false;
                if($contraints = $field->getConstraints())
                {
                  $options["constraints"] = $contraints;
                }
                if($field->getSymfonyFormType())
                {
                  $event->getForm()->add($field->getFieldname(), $field->getSymfonyFormType(), $options);
                }
              }
            }
          }
        }
      );
    }
  }

  /**
   * @param FormBuilderInterface $builder
   * @param FieldInterface $field
   * @param FormMapper $formMapper
   */
  protected function addFieldToBuildForm(FormBuilderInterface $builder, FieldInterface $field, FormMapper $formMapper)
  {
    if($field->getUsedGeneratedForm())
    {
      $options = $field->getFieldOptions();
      $options["label"] = $field->getEntitled() ?? false;
      if($contraints = $field->getConstraints())
      {
        $options["constraints"] = $contraints;
      }

      $object = $builder->getData();
      if($field instanceof UploadField && $this->container && $object)
      {
        /** @var FieldFileMapping $fieldMapping */
        if($fieldMapping = $this->container->get("austral.entity.mapping")->getFieldsMappingByFieldname($object->getClassnameForMapping(), FieldFileMapping::class, $field->getFieldname()))
        {
          if($filePath = $this->filePath($object, $field->getFieldname()))
          {
            $parameters = $field->getUploadFileParameters();

            $isImage  = AustralTools::isImage($filePath);
            $parameters["file"]["reelFilename"] = $fieldMapping->getFilename($object, true);
            $parameters["file"]['path'] = array(
              "view"          =>  $isImage ? $this->image($object, $field->getFieldname(), "original", "i", 1000, 1000) : null,
              "original"      =>  $isImage ? $this->image($object, $field->getFieldname()) : null,
              "download"      =>  $this->download($object, $field->getFieldname()),
              "absolute"      =>  $filePath,
            );
            $parameters["file"]['infos']["mimeType"] = AustralTools::mimeType($filePath);
            $parameters["file"]['infos']["extension"] = AustralTools::extension($filePath);
            $parameters["file"]['infos']["size"] = filesize($filePath);
            $parameters["file"]['infos']["sizeHuman"] = AustralTools::humanizeSize($filePath);
            if($isImage)
            {
              $imageDimension = $isImage ? AustralTools::imageDimension($filePath, true) : array(
                "width"             =>  null,
                "height"            =>  null,
              );
              $aspectRatio = null;
              if(array_key_exists("width", $imageDimension) && array_key_exists("height", $imageDimension))
              {
                if($imageDimension["width"] > 0 && $imageDimension["height"] > 0)
                {
                  $aspectRatio = $imageDimension["width"]/$imageDimension["height"];
                }
              }
              $parameters["file"]['infos']["imageSize"] = $imageDimension ? implode(" x ", $imageDimension) : null;
              $parameters["file"]['infos']["imageDimension"] = $imageDimension;
              $parameters["file"]['infos']["aspectRatio"] = $aspectRatio;
            }
            $field->setUploadFileParameters($parameters);
          }
        }
      }


      if($field->getSymfonyFormType())
      {
        $builder->add($field->getFieldname(), $field->getSymfonyFormType(), $options);
      }
      elseif($field instanceof MultiField && $field->getFields())
      {
        foreach($field->getFields() as $field)
        {
          $this->addFieldToBuildForm($builder, $field, $formMapper);
        }
      }

      if(get_class($field) == CollectionEmbedField::class || AustralTools::usedClass(get_class($field), CollectionEmbedField::class))
      {
        if(count($field->getCollectionsForms()) > 0)
        {
          /** @var FieldInterface $collectionsForm */
          foreach($field->getCollectionsForms() as $collectionsForm)
          {
            $this->addFieldToBuildForm($builder, $collectionsForm, $formMapper);
          }
        }
        else
        {
          $builder->add($field->getFieldname(), $field->getSymfonyFormType(), $options);
        }
      }
    }
  }

  /**
   * @param FileInterface|null $object
   * @param string $fieldname
   *
   * @return string|null
   * @throws \Exception
   */
  public function filePath(?FileInterface $object, string $fieldname): ?string
  {
    /** @var FieldFileMapping $fieldMapping */
    if($fieldMapping = $this->container->get("austral.entity.mapping")->getFieldsMappingByFieldname($object->getClassnameForMapping(), FieldFileMapping::class, $fieldname))
    {
      return $fieldMapping->getObjectFilePath($object);
    }
    return null;
  }

  /**
   * Download Url initializations
   *
   * @param FileInterface $object
   * @param string $fieldname
   * @param array $params
   *
   * @return string|null
   */
  public function download(FileInterface $object, string $fieldname, array $params = array()): ?string
  {
    return $this->container->get('austral.entity_file.link.generator')->download($object, $fieldname, $params);
  }

  /**
   * Download Url initializations
   *
   * @param FileInterface $object
   * @param string $fieldname
   * @param string|null $mode
   * @param int|null $width
   * @param int|null $height
   * @param string|null $type
   * @param array $params
   *
   * @return string|null
   */
  public function image(FileInterface $object, string $fieldname, ?string $type = "original", ?string $mode = "resize", int $width = null, int $height = null, array $params = array()): ?string
  {
    return $this->container->get('austral.entity_file.link.generator')->image($object, $fieldname, $type, $mode, $width, $height, $params);
  }

}