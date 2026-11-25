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

use Austral\FormBundle\Field\Base\BaseSelectField;

use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field Entity input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class EnumField extends BaseSelectField
{

  /**
   * @var string
   */
  protected string $enumClass;

  /**
   * @param $fieldname
   * @param string $enumClass
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, string $enumClass, array $options = array()): EnumField
  {
    return new EnumField($fieldname, $enumClass, $options);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param string $enumClass
   * @param array $options
   */
  public function __construct($fieldname, string $enumClass, array $options = array())
  {
    parent::__construct($fieldname, array(), $options);
    $this->symfonyFormType = EnumType::class;
    $this->enumClass = $enumClass;
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions["class"] = $this->enumClass;
    unset($fieldOptions['choices']);
    return $fieldOptions;
  }

}