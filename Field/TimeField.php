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

use Symfony\Component\Form\Extension\Core\Type\TimeType;

/**
 * Austral Field Time Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class TimeField extends Field
{

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $options = array()): TimeField
  {
    return new self($fieldname, $options);
  }

  /**
   * TextField constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    $options = array_merge(array(
      "fieldOptions"  =>  array(
        "widget"  =>  "single_text"
      )
    ), $options);
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = TimeType::class;
  }

}