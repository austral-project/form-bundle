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

use Austral\Contracts\Entity\EntityInterface;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Mapper\GroupFields;
use Austral\ToolsBundle\AustralTools;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Constraints;

use function Symfony\Component\String\u;

/**
 * Austral Abstract Field.
 * @template T of object
 *
 * @implements FieldInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 *
 * @abstract
 */
abstract class Field implements FieldInterface
{
  /**
   * @var string
   */
  protected string $fieldname;

  /**
   * @var class-string<FormTypeInterface>|string|null
   */
  protected ?string $symfonyFormType = null;

  /**
   * @var array<string, mixed>
   */
  protected array $options;

  /**
   * @var array<string|int, Constraint>
   */
  protected array $constraints = [];

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
  protected ?string $modalId = null;

  /**
   * @var bool
   */
  protected bool $isInModal = false;

  /**
   * @var array<string, mixed>
   */
  protected array $editorFields = [];

  /**
   * @var bool
   */
  protected bool $isDefaultTemplate = true;


  /**
   * EntityFieldList constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   */
  public function __construct(string $fieldname, array $options = [])
  {
    $this->fieldname = $fieldname;
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
    if($this->options['template']["path"])
    {
      $this->isDefaultTemplate = false;
    }
  }

  /**
   * @param OptionsResolver $resolver
   *
   * @return void
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults(array(
        "required"            =>  false,
        "formMapper"          =>  null,
        "class"               =>  null,
        "isView"              =>  true,
        "entitled"            =>  "fields.{$this->fieldname}.entitled",
        "placeholder"         =>  null,
        "picto"               =>  null,
        "labelPosition"       =>  "external",
        "before"              =>  function(OptionsResolver $subResolver) {
          $subResolver->setDefaults(array(
              "path"            =>  null,
              "html"            =>  null,
              "twigComponent"  =>  function(OptionsResolver $threeResolver) {
                $threeResolver->setDefaults(array(
                    "name"            =>  null,
                    "parameters"      =>  array(),
                  )
                );
                $threeResolver->addAllowedTypes("name", array('null', "string"));
                $threeResolver->addAllowedTypes("parameters", array('null', "array"));
              },
            )
          );
          $subResolver->addAllowedTypes("path", array('null', "string"));
          $subResolver->addAllowedTypes("html", array('null', "string"));
        },
        "after"              =>  function(OptionsResolver $subResolver) {
          $subResolver->setDefaults(array(
              "path"        =>  null,
              "html"        =>  null,
              "twigComponent"  =>  function(OptionsResolver $threeResolver) {
                $threeResolver->setDefaults(array(
                    "name"            =>  null,
                    "parameters"      =>  array(),
                  )
                );
                $threeResolver->addAllowedTypes("name", array('null', "string"));
                $threeResolver->addAllowedTypes("parameters", array('null', "array"));
              }
            )
          );
          $subResolver->addAllowedTypes("path", array('null', "string"));
          $subResolver->addAllowedTypes("html", array('null', "string"));
        },
        "attr"                =>  array(
          "class"               =>  null,
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

        "container"           =>  array(
          "class"               =>  null,
        ),
        "autoConstraints"      =>  true,
        "helper"              =>  null,

        "template"            =>  function(OptionsResolver $subResolver) {
          $subResolver->setDefaults(array(
              "path"      =>  null,
              "vars"      =>  []
            )
          );
          $subResolver->addAllowedTypes("path", array('null', "string"))
            ->addAllowedTypes("vars", array('array'));
        },

        "setter"              =>  null,
        "getter"              =>  null,

        "modal"               =>  function(OptionsResolver $subResolver) {
          $subResolver->setDefaults(array(
              "id"      =>  null,
              "isIn"    =>  false
            )
          );
          $subResolver->addAllowedTypes("id", array('null', "string"))
          ->addAllowedTypes("isIn", array('bool'));
        },
        "fieldOptions"        => [],
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
      ->addAllowedTypes("labelPosition", array('null', "string"))

      ->addAllowedTypes("autoConstraints", array('bool'))
      ->addAllowedTypes("helper", array('null', "string"))

      ->addAllowedTypes("attr", array('array'))

      ->addAllowedTypes("fieldOptions", array('array'))
      ->addAllowedTypes("container", array('array'))

      ->addAllowedTypes("mapped", array('bool'));
  }

  /**
   * @inheritDoc
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * @inheritDoc
   */
  public function setOptions(array $options): self
  {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = $this->options['fieldOptions'];
    $fieldOptions['attr'] = array_key_exists("attr", $fieldOptions) ? $fieldOptions['attr'] : [];
    $fieldOptions["attr"] = array_merge($fieldOptions['attr'], $this->options['attr']);
    $fieldOptions["required"] = array_key_exists("required", $fieldOptions) ? $fieldOptions["required"] : $this->getRequired();

    if($this->options['placeholder'] && !array_key_exists("placeholder", $fieldOptions['attr']))
    {
      $fieldOptions["attr"]["placeholder"] = $this->options['placeholder'];
    }
    $fieldOptions["setter"] = array_key_exists("setter", $fieldOptions) ? $fieldOptions["setter"] : $this->options['setter'];
    $fieldOptions["getter"] = array_key_exists("getter", $fieldOptions) ? $fieldOptions["getter"] : $this->options['getter'];
    $fieldOptions["mapped"] = array_key_exists("mapped", $fieldOptions) ? $fieldOptions["mapped"] : $this->options['mapped'];
    return $fieldOptions;
  }

  /**
   * @inheritDoc
   */
  public function setFieldOptions(array $fieldOptions = []): self
  {
    $this->options['fieldOptions'] = $fieldOptions;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getFieldname(): string
  {
    return $this->fieldname;
  }

  /**
   * @inheritDoc
   */
  public function setFieldname(string $fieldname): self
  {
    $this->fieldname = $fieldname;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getFormMapper(): ?FormMapper
  {
    return $this->options['formMapper'];
  }

  /**
   * @inheritDoc
   */
  public function setFormMapper(?FormMapper $formMapper = null): self
  {
    $this->options['formMapper'] = $formMapper;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getEntitled(): bool|string|null
  {
    return $this->options['entitled'];
  }

  /**
   * @inheritDoc
   */
  public function setEntitled(bool|string|null $entitled = null): self
  {
    $this->options['entitled'] = $entitled;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getAttrClass(): string
  {
    return "field-content-".u($this->getClassname())->camel()->toString()." ".$this->options['container']['class'];
  }

  /**
   * @inheritDoc
   */
  public function getClassname(): string
  {
    return (new \ReflectionClass($this))->getShortName();
  }

  /**
   * @inheritDoc
   */
  public function getSymfonyFormTypeClass(): string
  {
    return u($this->getSymfonyFormTypeName())->camel()->toString();
  }

  /**
   * @inheritDoc
   */
  public function getSymfonyFormTypeName(): string
  {
    return $this->getSymfonyFormType() ? join('', array_slice(explode('\\', $this->getSymfonyFormType()), -1)) : '';
  }

  /**
   * @inheritDoc
   */
  public function getSymfonyFormType(): ?string
  {
    return $this->symfonyFormType;
  }

  /**
   * @inheritDoc
   */
  public function setSymfonyFormType(string $symfonyFormType): self
  {
    $this->symfonyFormType = $symfonyFormType;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function addOption(string $key, $value): self
  {
    $this->options[$key] = $value;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function addConstraint(Constraint $addConstraint, bool $replace = false): self
  {
    $addContraintCheck = true;
    foreach($this->constraints as $key => $constraint)
    {
      if($addConstraint instanceof $constraint)
      {
        if($replace)
        {
          unset($this->constraints[$key]);
        }
        else
        {
          $addContraintCheck = false;
        }
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
   * initRequiredByConstraint
   *
   * @param Constraint $constraint
   * @return void
   */
  protected function initRequiredByConstraint(Constraint $constraint): void
  {
    if($constraint instanceof Constraints\NotNull) {
      $this->setRequired(true);
    }
  }

  /**
   * @inheritDoc
   */
  public function getConstraints(): array
  {
    return $this->constraints;
  }

  /**
   * @inheritDoc
   */
  public function setConstraints(array $constraints = []): self
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
   * @inheritDoc
   */
  public function removeContraintByKey(int|string $key): self
  {
    if(array_key_exists($key, $this->constraints))
    {
      unset($this->constraints[$key]);
    }
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function removeContraintByClass(string $contraintClass): self
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
   * @inheritDoc
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
   * @inheritDoc
   */
  public function getUsedGeneratedForm(): bool
  {
    return $this->usedGeneratedForm;
  }

  /**
   * @inheritDoc
   */
  public function setUsedGeneratedForm(bool $usedGeneratedForm): self
  {
    $this->usedGeneratedForm = $usedGeneratedForm;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function isInModal(): bool
  {
    return $this->options['modal']["isIn"];
  }

  /**
   * @inheritDoc
   */
  public function setIsInModal(bool $isInModal): self
  {
    $this->options['modal']["isIn"] = $isInModal;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getModalId(): ?string
  {
    return $this->options['modal']["id"];
  }

  /**
   * @inheritDoc
   */
  public function setModalId(?string $modalId = null): self
  {
    $this->options['modal']["id"] = $modalId;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getRequired(): ?bool
  {
    return $this->options["required"];
  }

  /**
   * @inheritDoc
   */
  public function setRequired(?bool $required): self
  {
    $this->options["required"] = $required;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function picto(): mixed
  {
    return $this->options['picto'];
  }

  /**
   * @inheritDoc
   */
  public function getIsView(): bool
  {
    $isView = false;
    if($this->options['isView'] !== null)
    {
      $isViewFunctionParams = [];
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
   * @inheritDoc
   */
  public function setIsView($isView): self
  {
    $this->options['isView'] = $isView;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getAutoConstraints(): bool
  {
    return $this->options['autoConstraints'];
  }

  /**
   * @inheritDoc
   */
  public function setAutoConstraints(bool $autoConstraints): self
  {
    $this->options['autoConstraints'] = $autoConstraints;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getTemplatePath(): ?string
  {
    return $this->options['template']['path'];
  }

  /**
   * @inheritDoc
   */
  public function setTemplatePath(string $templatePath): self
  {
    $this->options['template']["path"] = $templatePath;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getTemplateVars(): ?array
  {
    return $this->options['template']['vars'];
  }

  /**
   * @inheritDoc
   */
  public function setTemplateVars(array $templateVars): self
  {
    $this->options['template']["vars"] = $templateVars;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setGroupSize(?string $size = null): self
  {
    $this->options["group"]["size"] = $size;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getGroupSize(): ?string
  {
    return $this->options["group"]["size"] ? : GroupFields::SIZE_AUTO;
  }

  /**
   * @inheritDoc
   */
  public function setGroupClass(?string $class = null): self
  {
    $this->options["group"]["class"] = $class;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getGroupClass(): ?string
  {
    return $this->options["group"]["class"];
  }

  /**
   * @inheritDoc
   */
  public function getHelper(): ?string
  {
    return $this->options["helper"];
  }

  /**
   * @inheritDoc
   */
  public function getBefore(): ?array
  {
    return $this->options["before"];
  }

  /**
   * @inheritDoc
   */
  public function getAfter(): ?array
  {
    return $this->options["after"];
  }

  /**
   * @inheritDoc
   */
  public function setHelper(?string $helper = null): self
  {
    $this->options['helper'] = $helper;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function isDefaultTemplate(): bool
  {
    return $this->isDefaultTemplate;
  }

  /**
   * @inheritDoc
   */
  public function setDefaultTemplate(bool $isDefaultTemplate): self
  {
    $this->isDefaultTemplate = $isDefaultTemplate;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function isWidgetInput(): bool
  {
    return $this->widgetInput;
  }

  /**
   * @inheritDoc
   */
  public function getEditorFields(): array
  {
    return $this->editorFields;
  }

  /**
   * executeClosureFunction
   *
   * @param \Closure(object|null): mixed|string|array<int, string> $closureOrString
   * @param EntityInterface<T>|null $object
   * @param mixed $default
   * @return mixed
   */
  protected function executeClosureFunction(\Closure|string|array $closureOrString, EntityInterface|null $object = null, mixed $default = []): mixed
  {
    return AustralTools::executeFunction($this, $closureOrString, $object, $default);
  }

}