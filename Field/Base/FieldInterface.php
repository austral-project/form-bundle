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
use Symfony\Component\Validator\Constraint;

/**
 * Austral Field Interface.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
interface FieldInterface
{

  /**
   * Get options
   * @return array
   */
  public function getOptions(): array;

  /**
   * @param array $options
   *
   * @return FieldInterface
   */
  public function setOptions(array $options): FieldInterface;

  /**
   * @return array
   */
  public function getFieldOptions(): array;

  /**
   * @param array $fieldOptions
   *
   * @return FieldInterface
   */
  public function setFieldOptions(array $fieldOptions = array()): FieldInterface;

  /**
   * Get fieldname
   * @return string
   */
  public function getFieldname(): string;

  /**
   * Get fieldname
   *
   * @param $fieldname
   *
   * @return FieldInterface
   */
  public function setFieldname($fieldname): FieldInterface;

  /**
   * @return FormMapper|null
   */
  public function getFormMapper(): ?FormMapper;

  /**
   * @param FormMapper|null $formMapper
   *
   * @return FieldInterface
   */
  public function setFormMapper(FormMapper $formMapper = null): FieldInterface;

  /**
   * Get entitled
   * @return string|bool|null
   */
  public function getEntitled();

  /**
   * @param string|bool|null $entitled
   *
   * @return FieldInterface
   */
  public function setEntitled($entitled = null): FieldInterface;

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
   * @return string|null
   */
  public function getSymfonyFormType(): ?string;

  /**
   * @param string $symfonyFormType
   *
   * @return FieldInterface
   */
  public function setSymfonyFormType(string $symfonyFormType): FieldInterface;

  /**
   * @param string $key
   * @param $value
   *
   * @return FieldInterface
   */
  public function addOption(string $key, $value): FieldInterface;

  /**
   * @param Constraint $addConstraint
   *
   * @return FieldInterface
   */
  public function addConstraint(Constraint $addConstraint): FieldInterface;

  /**
   * Get constraints
   * @return array
   */
  public function getConstraints(): array;

  /**
   * @param array $constraints
   *
   * @return FieldInterface
   * @throws \Exception
   */
  public function setConstraints(array $constraints = array()): FieldInterface;

  /**
   * @param int $key
   *
   * @return $this
   */
  public function removeContraintByKey(int $key): FieldInterface;

  /**
   * @param string $contraintClass
   *
   * @return $this
   */
  public function removeContraintByClass(string $contraintClass): FieldInterface;

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
   * @return FieldInterface
   */
  public function setUsedGeneratedForm(bool $usedGeneratedForm): FieldInterface;

  /**
   * @return bool
   */
  public function isInPopin(): bool;

  /**
   * @param bool $isInPopin
   *
   * @return FieldInterface
   */
  public function setIsInPopin(bool $isInPopin): FieldInterface;

  /**
   * Get popinId
   * @return string|null
   */
  public function getPopinId(): ?string;

  /**
   * set popinId
   *
   * @param string|null $popinId
   *
   * @return FieldInterface
   */
  public function setPopinId(string $popinId = null): FieldInterface;

  /**
   * Get required
   * @return bool|null
   */
  public function getRequired(): ?bool;

  /**
   * @param bool|null $required
   *
   * @return FieldInterface
   */
  public function setRequired(?bool $required): FieldInterface;

  /**
   * @return mixed
   */
  public function picto();

  /**
   * Get isView
   * @return bool
   */
  public function getIsView(): bool;

  /**
   * Get isView
   * @return $this
   */
  public function setIsView($isView): FieldInterface;

  /**
   * Get autoConstraints
   * @return bool
   */
  public function getAutoConstraints(): bool;

  /**
   * @param bool $autoConstraints
   *
   * @return FieldInterface
   */
  public function setAutoConstraints(bool $autoConstraints): FieldInterface;

  /**
   * @return string|null
   */
  public function getTemplatePath(): ?string;

  /**
   * @param string $templatePath
   *
   * @return FieldInterface
   */
  public function setTemplatePath(string $templatePath): FieldInterface;

  /**
   * @param string|null $size
   *
   * @return FieldInterface
   */
  public function setGroupSize(?string $size = null): FieldInterface;

  /**
   * @return string|null
   */
  public function getGroupSize(): ?string;

  /**
   * @param string|null $class
   *
   * @return FieldInterface
   */
  public function setGroupClass(?string $class = null): FieldInterface;

  /**
   * @return string|null
   */
  public function getGroupClass(): ?string;
  /**
   * @return string|null
   */
  public function getHelper(): ?string;

  /**
   * @param string|null $helper
   *
   * @return FieldInterface
   */
  public function setHelper(string $helper = null): FieldInterface;

  /**
   * @return bool
   */
  public function isDefaultTemplate(): bool;

  /**
   * @param bool $isDefaultTemplate
   *
   * @return FieldInterface
   */
  public function setDefaultTemplate(bool $isDefaultTemplate): FieldInterface;

  /**
   * @return bool
   */
  public function isWidgetInput(): bool;

  /**
   * @return array
   */
  public function getEditorFields(): array;

}