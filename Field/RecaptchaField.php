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

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field RecaptchaField Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class RecaptchaField extends Field
{

  const FIELD_NAME = "_g-recaptcha-response";

  /**
   * @param array $options
   *
   * @return $this
   */
  public static function create(array $options = array()): RecaptchaField
  {
    return new self($options);
  }

  /**
   * TextField constructor.
   *
   * @param array $options
   */
  public function __construct(array $options = array())
  {
    parent::__construct(self::FIELD_NAME, $options);
    $this->symfonyFormType = HiddenType::class;
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('secretKey', null);
    $resolver->setDefault('publicKey', null);
    $resolver->setAllowedTypes('secretKey', array('string'));
    $resolver->setAllowedTypes('publicKey', array('string'));
  }

  /**
   * @return string|null
   */
  public function getSecretKey(): ?string
  {
    return $this->options["secretKey"];
  }

  /**
   * @return string|null
   */
  public function getPublicKey(): ?string
  {
    return $this->options["publicKey"];
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions['mapped'] = false;
    $fieldOptions['error_bubbling'] = false;
    $fieldOptions['attr']["data-recaptcha"] = $this->getPublicKey();
    $fieldOptions['attr']["data-type-action"] = "registry";
    return $fieldOptions;
  }

}