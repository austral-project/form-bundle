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

use Austral\FormBundle\Field\Base\FieldInterface;
use Austral\FormBundle\Field\CollectionEmbedField;
use Austral\FormBundle\Field\MultiField;
use Austral\FormBundle\Field\SelectField;
use Austral\FormBundle\Mapper\FormMapper;

use Austral\ToolsBundle\AustralTools;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
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
   * @var AuthorizationCheckerInterface
   */
  protected AuthorizationCheckerInterface $security;

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
   * @param AuthorizationCheckerInterface $security
   */
  public function __construct(AuthorizationCheckerInterface $security)
  {
    $this->security = $security;
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
      $this->addFieldToBuildForm($builder, $field);
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
   */
  protected function addFieldToBuildForm(FormBuilderInterface $builder, FieldInterface $field)
  {
    if($field->getUsedGeneratedForm())
    {
      $options = $field->getFieldOptions();
      $options["label"] = $field->getEntitled() ?? false;
      if($contraints = $field->getConstraints())
      {
        $options["constraints"] = $contraints;
      }

      if($field->getSymfonyFormType())
      {
        $builder->add($field->getFieldname(), $field->getSymfonyFormType(), $options);
      }
      elseif($field instanceof MultiField && $field->getFields())
      {
        foreach($field->getFields() as $field)
        {
          $this->addFieldToBuildForm($builder, $field);
        }
      }

      if(get_class($field) == CollectionEmbedField::class || AustralTools::usedClass(get_class($field), CollectionEmbedField::class))
      {
        if(count($field->getCollectionsForms()) > 0)
        {
          /** @var FieldInterface $collectionsForm */
          foreach($field->getCollectionsForms() as $collectionsForm)
          {
            $this->addFieldToBuildForm($builder, $collectionsForm);
          }
        }
        else
        {
          $builder->add($field->getFieldname(), $field->getSymfonyFormType(), $options);
        }
      }
    }
  }

}