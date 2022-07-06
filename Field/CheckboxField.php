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

/**
 * Austral Field checkbox Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class CheckboxField extends SwitchField
{
  /**
   * @var array
   */
  protected array $switchOptions;

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $options = array()): CheckboxField
  {
    return new self($fieldname, $options);
  }

  /**
   * CheckboxField constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
    if($this->isDefaultTemplate)
    {
      $this->options['templatePath'] = "checkbox-field.html.twig";
    }
  }

}