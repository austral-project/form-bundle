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
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Austral Field Interface.
 * @template T of object
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
interface FieldInterface
{

  /**
   * Get options
   * @return array<string, mixed>
   */
  public function getOptions(): array;

  /**
   * @param array<string, mixed> $options
   *
   * @return $this
   */
  public function setOptions(array $options): self;

  /**
   * @return array<string, mixed>
   */
  public function getFieldOptions(): array;

  /**
   * @param array<string, mixed> $fieldOptions
   *
   * @return $this
   */
  public function setFieldOptions(array $fieldOptions = []): self;

  /**
   * Get fieldname
   * @return string
   */
  public function getFieldname(): string;

  /**
   * Get fieldname
   *
   * @param string $fieldname
   *
   * @return $this
   */
  public function setFieldname(string $fieldname): self;

  /**
   * @return FormMapper<T>|null
   */
  public function getFormMapper(): ?FormMapper;

  /**
   * @param FormMapper<T>|null $formMapper
   *
   * @return $this
   */
  public function setFormMapper(?FormMapper $formMapper = null): self;

  /**
   * Get entitled
   * @return string|bool|null
   */
  public function getEntitled(): bool|string|null;

  /**
   * @param bool|string|null $entitled
   *
   * @return $this
   */
  public function setEntitled(bool|string|null $entitled = null): self;

  /**
   * @return string
   */
  public function getAttrClass(): string;

  /**
   * @return string
   */
  public function getClassname(): string;

  /**
   * @return string
   */
  public function getSymfonyFormTypeClass(): string;

  /**
   * @return string
   */
  public function getSymfonyFormTypeName(): string;

  /**
   * Get symfonyFormType
   * @return class-string<FormTypeInterface>|string|null
   */
  public function getSymfonyFormType(): ?string;

  /**
   * @param class-string<FormTypeInterface> $symfonyFormType
   *
   * @return $this
   */
  public function setSymfonyFormType(string $symfonyFormType): self;

  /**
   * @param string $key
   * @param mixed $value
   *
   * @return $this
   */
  public function addOption(string $key, mixed $value): self;

  /**
   * @param Constraint $addConstraint
   * @param bool $replace replaces an existing constraint of the same class
   *
   * @return $this
   */
  public function addConstraint(Constraint $addConstraint, bool $replace = false): self;

  /**
   * Get constraints
   * @return array<string|int, Constraint>
   */
  public function getConstraints(): array;

  /**
   * @param array<string|int, Constraint> $constraints
   *
   * @return $this
   * @throws \Exception
   */
  public function setConstraints(array $constraints = []): self;

  /**
   * @param int|string $key
   *
   * @return $this
   */
  public function removeContraintByKey(int|string $key): self;

  /**
   * @param string $contraintClass
   *
   * @return $this
   */
  public function removeContraintByClass(string $contraintClass): self;

  /**
   * @param string $contraintClass
   *
   * @return bool
   */
  public function hasContraint(string $contraintClass): bool;

  /**
   * Get usedGeneratedForm
   * @return bool
   */
  public function getUsedGeneratedForm(): bool;

  /**
   * @param bool $usedGeneratedForm
   *
   * @return $this
   */
  public function setUsedGeneratedForm(bool $usedGeneratedForm): self;

  /**
   * @return bool
   */
  public function isInModal(): bool;

  /**
   * @param bool $isInModal
   *
   * @return $this
   */
  public function setIsInModal(bool $isInModal): self;

  /**
   * Get modalId
   * @return string|null
   */
  public function getModalId(): ?string;

  /**
   * set modalId
   *
   * @param string|null $modalId
   *
   * @return $this
   */
  public function setModalId(?string $modalId = null): self;

  /**
   * Get required
   * @return bool|null
   */
  public function getRequired(): ?bool;

  /**
   * @param bool|null $required
   *
   * @return $this
   */
  public function setRequired(?bool $required): self;

  /**
   * @return mixed
   */
  public function picto(): mixed;

  /**
   * Get isView
   * @return bool
   */
  public function getIsView(): bool;

  /**
   * Get isView
   * @param bool $isView
   *
   * @return $this
   */
  public function setIsView(bool $isView): self;

  /**
   * Get autoConstraints
   * @return bool
   */
  public function getAutoConstraints(): bool;

  /**
   * @param bool $autoConstraints
   *
   * @return $this
   */
  public function setAutoConstraints(bool $autoConstraints): self;

  /**
   * @return string|null
   */
  public function getTemplatePath(): ?string;

  /**
   * @param string $templatePath
   *
   * @return $this
   */
  public function setTemplatePath(string $templatePath): self;

  /**
   * getTemplateVars
   *
   * @return array<string, mixed>|null
   */
  public function getTemplateVars(): ?array;

  /**
   * setTemplateVars
   * @param array<string, mixed> $templateVars
   *
   * @return $this
   */
  public function setTemplateVars(array $templateVars): self;

  /**
   * @param string|null $size
   *
   * @return $this
   */
  public function setGroupSize(?string $size = null): self;

  /**
   * @return string|null
   */
  public function getGroupSize(): ?string;

  /**
   * @param string|null $class
   *
   * @return $this
   */
  public function setGroupClass(?string $class = null): self;

  /**
   * @return string|null
   */
  public function getGroupClass(): ?string;

  /**
   * @return string|null
   */
  public function getHelper(): ?string;

  /**
   * Content displayed before the field (path, html, twigComponent).
   *
   * @return array<string, mixed>|null
   */
  public function getBefore(): ?array;

  /**
   * Content displayed after the field (path, html, twigComponent).
   *
   * @return array<string, mixed>|null
   */
  public function getAfter(): ?array;

  /**
   * @param string|null $helper
   *
   * @return $this
   */
  public function setHelper(?string $helper = null): self;

  /**
   * @return bool
   */
  public function isDefaultTemplate(): bool;

  /**
   * @param bool $isDefaultTemplate
   *
   * @return $this
   */
  public function setDefaultTemplate(bool $isDefaultTemplate): self;

  /**
   * @return bool
   */
  public function isWidgetInput(): bool;

  /**
   * @return array<string, mixed>
   */
  public function getEditorFields(): array;

}