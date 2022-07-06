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
use Austral\FormBundle\Form\Type\FormTypeInterface;

/**
 * Austral Field FormType Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class FormTypeField extends Field
{

  /**
   * @param $fieldname
   * @param FormTypeInterface $fieldTypeClass
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, FormTypeInterface $fieldTypeClass, array $options = array()): FormTypeField
  {
    return new FormTypeField($fieldname, $fieldTypeClass, $options);
  }

  /**
   * FormTypeField constructor.
   *
   * @param FormTypeInterface $fieldTypeClass
   * @param string $fieldname
   * @param array $options
   */
  public function __construct(string $fieldname, FormTypeInterface $fieldTypeClass, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = get_class($fieldTypeClass);
    if($this->isDefaultTemplate)
    {
      $this->options["templatePath"] = "form-type-field.html.twig";
    }
  }

}