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
use Symfony\Component\Form\FormTypeInterface as SymfonyFormTypeInterface;

/**
 * Austral FormType.
 * @template T of object
 *
 * @implements FormTypeInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class FormType extends AbstractType implements FormTypeInterface
{
  /**
   * @var AuthorizationCheckerInterface|null
   */
  protected ?AuthorizationCheckerInterface $security = null;

  /**
   * @var string|null
   */
  protected ?string $class = null;

  /**
   * @var ?FormMapper<T>
   */
  protected ?FormMapper $formMapper = null;

  /**
   * @var array<string, FormMapper<T>>
   */
  protected array $formMappers = [];

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
  public function setSecurity(?AuthorizationCheckerInterface $security = null): self
  {
    $this->security = $security;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getBlockPrefix(): ?string
  {
    return $this->getFormMapper()?->getName();
  }

  /**
   * @param FormMapper<T> $formMapper
   *
   * @return $this
   */
  public function setFormMapper(FormMapper $formMapper): self
  {
    if(!$this->formMapper)
    {
      $this->formMapper = $formMapper;
    }
    return $this;
  }

  /**
   * @param string $key
   * @param FormMapper<T> $formMapper
   *
   * @return $this
   */
  public function addFormMappers(string $key, FormMapper $formMapper): self
  {
    $this->formMappers[$key] = $formMapper;
    return $this;
  }

  /**
   * @param FormBuilderInterface|null $builder
   *
   * @return FormMapper<T>|null
   */
  protected function getFormMapper(?FormBuilderInterface $builder = null): ?FormMapper
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
        return AustralTools::valueByKey($this->formMapper?->getAllSubFormMapper() ?? [], $attribute["formMapperKey"]);
      }
      elseif($this->formMapper)
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
  public function setClass(string $class): self
  {
    $this->class = $class;
    return $this;
  }

  /**
   * @param mixed $role
   *
   * @return bool
   */
  protected function isGranted(mixed  $role): bool
  {
    return $this->security?->isGranted($role) ?? false;
  }

  /**
   * @inheritDoc
   */
  public function configureOptions(OptionsResolver $resolver): void
  {
    $defaultParameters = array(
      'validation_groups'   =>  $this->formMapper?->getOption("validation_groups"),
      "attr"                =>  [],
      "translation_domain"  =>  $this->formMapper?->getOption("translation_domain")
    );
    if($this->formMapper)
    {
      if(!$this->formMapper->getOption("html5_validate", false))
      {
        $defaultParameters['attr']["novalidate"] = "novalidate";
      }
      if($uniqueFields = $this->formMapper->getUniqueFields())
      {
        foreach($uniqueFields as $uniqueField)
        {
          $defaultParameters["constraints"][] = new UniqueEntity(fields: $uniqueField, message: "form.errors.unique");
        }
      }
    }

    $resolver->setDefaults($defaultParameters);
  }

  /**
   * @param FormBuilderInterface $builder
   * @param array<string, mixed> $options
   *
   * @return void
   */
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $formMapper = $this->getFormMapper($builder);
    if($formMapper)
    {
      /** @var FieldInterface<T> $field */
      foreach($formMapper->allFields() as $field)
      {
        $this->addFieldToBuildForm($builder, $field);
        $builder->addEventListener(
          FormEvents::PRE_SUBMIT,
          function(FormEvent $event) use($field)
          {
            $formData = $event->getData() ?? [];
            if(array_key_exists($field->getFieldname(), $formData))
            {
              $data = $event->getData()[$field->getFieldname()];
              if($field instanceof SelectField && $field->tagsActivated())
              {
                $choices = [];
                if(is_array($data)){
                  foreach($data as $choice){
                    $choices[$choice] = $choice;
                  }
                }
                else{
                  $choices[$data] = $data;
                }
                $this->buildFormSelectField($field, $choices, $event);

              }
            }
          }
        );
        $builder->addEventListener(
          FormEvents::PRE_SET_DATA,
          function(FormEvent $event) use($field) {
            if($data = $event->getData())
            {
              if($field instanceof SelectField && $field->tagsActivated())
              {
                if($choices = $field->getChoicesToTagsActivated($data))
                {
                  $this->buildFormSelectField($field, $choices, $event);
                }
              }
            }
          }
        );
      }
    }
  }

  /**
   * buildFormSelectField
   *
   * @param SelectField<T> $field
   * @param array<string, mixed> $choices
   * @param FormEvent $event
   *
   * @return void
   */
  protected function buildFormSelectField(SelectField $field, array $choices, FormEvent $event): void
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

  /**
   * @param FormBuilderInterface $builder
   * @param FieldInterface<T> $field
   *
   * @return void
   */
  protected function addFieldToBuildForm(FormBuilderInterface $builder, FieldInterface $field): void
  {
    if($field->getUsedGeneratedForm())
    {
      $options = $field->getFieldOptions();
      $options["label"] = $field->getEntitled() ?? false;
      if($contraints = $field->getConstraints())
      {
        $options["constraints"] = $contraints;
      }
      $symfonyFormType = $field->getSymfonyFormType();
      if($symfonyFormType && (class_exists($symfonyFormType) && is_subclass_of($symfonyFormType, SymfonyFormTypeInterface::class)))
      {
        $builder->add($field->getFieldname(), $symfonyFormType, $options);
      }
      elseif($field instanceof MultiField)
      {
        /** @var FieldInterface<T> $subField */
        foreach($field->getFields() as $subField)
        {
          $this->addFieldToBuildForm($builder, $subField);
        }
      }

      if($field instanceof CollectionEmbedField || AustralTools::usedClass(get_class($field), CollectionEmbedField::class))
      {
        /** @var CollectionEmbedField<T> $field */
        if(count($field->getCollectionsForms()) > 0)
        {
          /** @var FieldInterface<T> $collectionsForm */
          foreach($field->getCollectionsForms() as $collectionsForm)
          {
            $this->addFieldToBuildForm($builder, $collectionsForm);
          }
        }
        //elseif($symfonyFormType && (class_exists($symfonyFormType) && is_subclass_of($symfonyFormType, SymfonyFormTypeInterface::class)))
        //{
          //$builder->add($field->getFieldname(), $symfonyFormType, $options);
        //}
      }
    }
  }

}
