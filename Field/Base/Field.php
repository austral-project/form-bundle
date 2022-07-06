<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Field\Base;

use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Mapper\GroupFields;
use Austral\ToolsBundle\AustralTools;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Constraints;

use function Symfony\Component\String\u;

/**
 * Austral Abstract Field.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
abstract class Field implements FieldInterface
{
  /**
   * @var string
   */
  protected string $fieldname;

  /**
   * @var string|null
   */
  protected ?string $symfonyFormType = null;

  /**
   * @var array
   */
  protected array $options;

  /**
   * @var array
   */
  protected array $constraints = array();

  /**
   * @var bool
   */
  protected bool $usedGeneratedForm = true;

  /**
   * @var bool
   */
  protected bool $widgetInput = true;

  /**
   * @var string|null
   */
  protected ?string $popinId = null;

  /**
   * @var bool
   */
  protected bool $isInPopin = false;

  /**
   * @var array
   */
  protected array $editorFields = array();

  /**
   * @var bool
   */
  protected bool $isDefaultTemplate = true;


  /**
   * EntityFieldList constructor.
   *
   * @param $fieldname
   * @param array $options
   *
   */
  public function __construct($fieldname, array $options = array())
  {
    $this->fieldname = $fieldname;

    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);

    if($this->options['templatePath'])
    {
      $this->isDefaultTemplate = false;
    }

  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
        "required"            =>  false,
        "formMapper"          =>  null,
        "class"               =>  null,
        "isView"              =>  true,
        "entitled"            =>  "fields.{$this->fieldname}.entitled",
        "placeholder"         =>  null,
        "picto"               =>  null,
        "attr"                =>  array(
          "class"               =>  null
        ),
        "group"               =>  function(OptionsResolver $subResolver) {
          $subResolver->setDefaults(array(
              "size"        =>  null,
              "class"       =>  null,
            )
          );
          $subResolver->addAllowedTypes("class", array('null', "string"));
          $subResolver->addAllowedTypes("size", array('null', "string"));
        },

        "container"           =>  function(OptionsResolver $subResolver) {
          $subResolver->setDefaults(array(
              "class"      =>  null,
            )
          );
          $subResolver->addAllowedTypes("class", array('null', "string"));
        },

        "autoConstraints"      =>  true,
        "helper"              =>  null,

        "templatePath"        =>  null,

        "setter"              =>  null,
        "getter"              =>  null,

        "popin"               =>  function(OptionsResolver $subResolver) {
          $subResolver->setDefaults(array(
              "id"      =>  null,
              "isIn"    =>  false
            )
          );
          $subResolver->addAllowedTypes("id", array('null', "string"))
          ->addAllowedTypes("isIn", array('bool'));
        },
        "fieldOptions"        => array(),
        "mapped"              =>  true
      )
    );

    $resolver->addAllowedTypes("required", array('bool'))
      ->addAllowedTypes("formMapper", array('null', FormMapper::class))
      ->addAllowedTypes("class", array('null', "string"))
      ->addAllowedTypes("isView", array('bool', \Closure::class, "array"))
      ->addAllowedTypes("entitled", array('null', "bool", "string"))
      ->addAllowedTypes("placeholder", array('null', "string"))
      ->addAllowedTypes("picto", array('null', "string"))

      ->addAllowedTypes("autoConstraints", array('bool'))
      ->addAllowedTypes("helper", array('null', "string"))
      ->addAllowedTypes("templatePath", array('null', "string"))

      ->addAllowedTypes("attr", array('array'))

      ->addAllowedTypes("fieldOptions", array('array'))

      ->addAllowedTypes("mapped", array('bool'));
  }

  /**
   * Get options
   * @return array
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * @param array $options
   *
   * @return FieldInterface
   */
  public function setOptions(array $options): FieldInterface
  {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
    return $this;
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = $this->options['fieldOptions'];
    $fieldOptions['attr'] = array_key_exists("attr", $fieldOptions) ? $fieldOptions['attr'] : array();
    $fieldOptions["attr"] = array_merge($fieldOptions['attr'], $this->options['attr']);
    $fieldOptions["required"] = array_key_exists("required", $fieldOptions) ? $fieldOptions["required"] : $this->getRequired();
    $fieldOptions["attr"]["placeholder"] = array_key_exists("placeholder", $fieldOptions['attr']) ? $fieldOptions['attr']["placeholder"] : $this->options['placeholder'];
    $fieldOptions["setter"] = array_key_exists("setter", $fieldOptions) ? $fieldOptions["setter"] : $this->options['setter'];
    $fieldOptions["getter"] = array_key_exists("getter", $fieldOptions) ? $fieldOptions["getter"] : $this->options['getter'];
    $fieldOptions["mapped"] = array_key_exists("mapped", $fieldOptions) ? $fieldOptions["mapped"] : $this->options['mapped'];
    return $fieldOptions;
  }

  /**
   * @param array $fieldOptions
   *
   * @return FieldInterface
   */
  public function setFieldOptions(array $fieldOptions = array()): FieldInterface
  {
    $this->options['fieldOptions'] = $fieldOptions;
    return $this;
  }

  /**
   * Get fieldname
   * @return string
   */
  public function getFieldname(): string
  {
    return $this->fieldname;
  }

  /**
   * Get fieldname
   *
   * @param $fieldname
   *
   * @return FieldInterface
   */
  public function setFieldname($fieldname): FieldInterface
  {
    $this->fieldname = $fieldname;
    return $this;
  }

  /**
   * @return FormMapper|null
   */
  public function getFormMapper(): ?FormMapper
  {
    return $this->options['formMapper'];
  }

  /**
   * @param FormMapper|null $formMapper
   *
   * @return FieldInterface
   */
  public function setFormMapper(FormMapper $formMapper = null): FieldInterface
  {
    $this->options['formMapper'] = $formMapper;
    return $this;
  }

  /**
   * Get entitled
   * @return string|bool|null
   */
  public function getEntitled()
  {
    return $this->options['entitled'];
  }

  /**
   * @param string|bool|null $entitled
   *
   * @return FieldInterface
   */
  public function setEntitled($entitled = null): FieldInterface
  {
    $this->options['entitled'] = $entitled;
    return $this;
  }

  /**
   * @return string
   */
  public function getAttrClass(): string
  {
    return "field-content-".u($this->getClassname())->camel()->toString()." ".$this->options['container']['class'];
  }

  /**
   * @return string
   */
  public function getClassname(): string
  {
    return (new \ReflectionClass($this))->getShortName();
  }

  /**
   * @return string
   */
  public function getSymfonyFormTypeClass(): string
  {
    return u($this->getSymfonyFormTypeName())->camel()->toString();
  }

  /**
   * @return string
   */
  public function getSymfonyFormTypeName(): string
  {
    return join('', array_slice(explode('\\', $this->getSymfonyFormType()), -1));
  }

  /**
   * Get symfonyFormType
   * @return string|null
   */
  public function getSymfonyFormType(): ?string
  {
    return $this->symfonyFormType;
  }

  /**
   * @param string $symfonyFormType
   *
   * @return FieldInterface
   */
  public function setSymfonyFormType(string $symfonyFormType): FieldInterface
  {
    $this->symfonyFormType = $symfonyFormType;
    return $this;
  }

  /**
   * @param string $key
   * @param $value
   *
   * @return FieldInterface
   */
  public function addOption(string $key, $value): FieldInterface
  {
    $this->options[$key] = $value;
    return $this;
  }

  /**
   * @param Constraint $addConstraint
   *
   * @return FieldInterface
   */
  public function addConstraint(Constraint $addConstraint): FieldInterface
  {
    $addContraintCheck = true;
    foreach($this->constraints as $constraint)
    {
      if($addConstraint instanceof $constraint)
      {
        $addContraintCheck = false;
      }
    }
    if($addContraintCheck)
    {
      $this->initRequiredByConstraint($addConstraint);
      $this->constraints[] = $addConstraint;
    }
    return $this;
  }

  /**
   * @param Constraint $constraint
   *
   * @return void
   */
  protected function initRequiredByConstraint(Constraint $constraint)
  {
    if($constraint instanceof Constraints\NotNull) {
      $this->setRequired(true);
    }
  }

  /**
   * Get constraints
   * @return array
   */
  public function getConstraints(): array
  {
    return $this->constraints;
  }

  /**
   * @param array $constraints
   *
   * @return FieldInterface
   * @throws \Exception
   */
  public function setConstraints(array $constraints = array()): FieldInterface
  {
    /** @var Constraint $constraint */
    foreach($constraints as $constraint) {
      if(!$constraint instanceof Constraint) {
        throw new \Exception("Your add a constraint is not instance of " . Constraint::class);
      }
      $this->initRequiredByConstraint($constraint);
    }
    $this->constraints = $constraints;
    return $this;
  }

  /**
   * @param int $key
   *
   * @return $this
   */
  public function removeContraintByKey(int $key): Field
  {
    if(array_key_exists($key, $this->constraints))
    {
      unset($this->constraints[$key]);
    }
    return $this;
  }

  /**
   * @param string $contraintClass
   *
   * @return $this
   */
  public function removeContraintByClass(string $contraintClass): Field
  {
    foreach($this->constraints as $key => $constraint)
    {
      if(get_class($constraint) === $contraintClass)
      {
        $this->removeContraintByKey($key);
      }
    }
    return $this;
  }

  /**
   * @param string $contraintClass
   *
   * @return bool
   */
  public function hasContraint(string $contraintClass): bool
  {
    foreach($this->constraints as $constraint)
    {
      if(get_class($constraint) === $contraintClass)
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Get usedGeneratedForm
   * @return bool
   */
  public function getUsedGeneratedForm(): bool
  {
    return $this->usedGeneratedForm;
  }

  /**
   * @param bool $usedGeneratedForm
   *
   * @return FieldInterface
   */
  public function setUsedGeneratedForm(bool $usedGeneratedForm): FieldInterface
  {
    $this->usedGeneratedForm = $usedGeneratedForm;
    return $this;
  }

  /**
   * @return bool
   */
  public function isInPopin(): bool
  {
    return $this->options['popin']["isIn"];
  }

  /**
   * @param bool $isInPopin
   *
   * @return Field
   */
  public function setIsInPopin(bool $isInPopin): Field
  {
    $this->options['popin']["isIn"] = $isInPopin;
    return $this;
  }

  /**
   * Get popinId
   * @return string|null
   */
  public function getPopinId(): ?string
  {
    return $this->options['popin']["id"];
  }

  /**
   * set popinId
   *
   * @param string|null $popinId
   *
   * @return Field
   */
  public function setPopinId(string $popinId = null): Field
  {
    $this->options['popin']["id"] = $popinId;
    return $this;
  }

  /**
   * Get required
   * @return bool|null
   */
  public function getRequired(): ?bool
  {
    return $this->options["required"];
  }

  /**
   * @param bool|null $required
   *
   * @return FieldInterface
   */
  public function setRequired(?bool $required): FieldInterface
  {
    $this->options["required"] = $required;
    return $this;
  }

  /**
   * @return mixed
   */
  public function picto()
  {
    return $this->options['picto'];
  }

  /**
   * Get isView
   * @return bool
   */
  public function getIsView(): bool
  {
    $isView = false;
    if($this->options['isView'] !== null)
    {
      $isViewFunctionParams = array();
      if(is_array($this->options['isView']))
      {
        $isViewFunction = $this->options['isView'][0];
        unset($this->options['isView'][0]);
        $isViewFunctionParams = $this->options['isView'];
      }
      else
      {
        $isViewFunction = $this->options['isView'];
      }

      if (\is_callable($isViewFunction))
      {
        $isView = call_user_func_array($isViewFunction, $isViewFunctionParams);
      }
      else
      {
        $isView = $this->options['isView'];
      }
    }
    return $isView;
  }

  /**
   * Get isView
   *
   * @param $isView
   *
   * @return FieldInterface
   */
  public function setIsView($isView): FieldInterface
  {
    $this->options['isView'] = $isView;
    return $this;
  }

  /**
   * Get autoConstraints
   * @return bool
   */
  public function getAutoConstraints(): bool
  {
    return $this->options['autoConstraints'];
  }

  /**
   * @param bool $autoConstraints
   *
   * @return FieldInterface
   */
  public function setAutoConstraints(bool $autoConstraints): FieldInterface
  {
    $this->options['autoConstraints'] = $autoConstraints;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getTemplatePath(): ?string
  {
    return $this->options['templatePath'];
  }

  /**
   * @param string $templatePath
   *
   * @return FieldInterface
   */
  public function setTemplatePath(string $templatePath): FieldInterface
  {
    $this->options['templatePath'] = $templatePath;
    return $this;
  }

  /**
   * @param string|null $size
   *
   * @return FieldInterface
   */
  public function setGroupSize(?string $size = null): FieldInterface
  {
    $this->options["group"]["size"] = $size;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getGroupSize(): ?string
  {
    return $this->options["group"]["size"] ? : GroupFields::SIZE_AUTO;
  }

  /**
   * @param string|null $class
   *
   * @return FieldInterface
   */
  public function setGroupClass(?string $class = null): FieldInterface
  {
    $this->options["group"]["class"] = $class;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getGroupClass(): ?string
  {
    return $this->options["group"]["class"];
  }

  /**
   * @return string|null
   */
  public function getHelper(): ?string
  {
    return $this->options["helper"];
  }

  /**
   * @param string|null $helper
   *
   * @return FieldInterface
   */
  public function setHelper(string $helper = null): FieldInterface
  {
    $this->options['helper'] = $helper;
    return $this;
  }

  /**
   * @return bool
   */
  public function isDefaultTemplate(): bool
  {
    return $this->isDefaultTemplate;
  }

  /**
   * @param bool $isDefaultTemplate
   *
   * @return FieldInterface
   */
  public function setDefaultTemplate(bool $isDefaultTemplate): FieldInterface
  {
    $this->isDefaultTemplate = $isDefaultTemplate;
    return $this;
  }

  /**
   * @return bool
   */
  public function isWidgetInput(): bool
  {
    return $this->widgetInput;
  }

  /**
   * @return array
   */
  public function getEditorFields(): array
  {
    return $this->editorFields;
  }

  /**
   * @return string|null|\Closure|array
   */
  protected function executeClosureFunction($closureOrString, $object = null, $default = array())
  {
    if($closureOrString instanceof \Closure)
    {
      if($object)
      {
        return $closureOrString->call($this, $object);
      }
      return $default;
    }
    if($closureOrString)
    {
      $value = null;
      $parameter = null;
      if(is_array($closureOrString))
      {
        list($closureOrString, $parameter) = $closureOrString;
      }
      if(method_exists($object, $closureOrString))
      {
        $value = $object->{$closureOrString}($parameter);
      }
      else
      {
        $getter = AustralTools::createGetterFunction($closureOrString);
        if(method_exists($object, $getter))
        {
          $value = $object->$getter($parameter);
        }
      }
      if($value instanceof \DateTime) {
        $value = $value->format($parameter);
      }
      return $value;
    }
    return $default;
  }

}