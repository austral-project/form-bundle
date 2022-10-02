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
use Austral\ToolsBundle\AustralTools;


/**
 * Austral Field Select Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class SelectField extends BaseSelectField
{

  /**
   * @var array
   */
  protected array $choices = array();

  /**
   * @param $fieldname
   * @param array $choices
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $choices = array(), array $options = array()): SelectField
  {
    return new self($fieldname, $choices, $options);
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions["attr"]['autocomplete'] = AustralTools::getValueByKey(AustralTools::getValueByKey($fieldOptions, "attr", array()), "autocomplete", "off");
    return $fieldOptions;
  }


}