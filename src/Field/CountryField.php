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
use Symfony\Component\Form\Extension\Core\Type\CountryType;


/**
 * Austral Field Country Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class CountryField extends BaseSelectField
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
  public static function create($fieldname, array $options = array()): CountryField
  {
    return new self($fieldname, $options);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    parent::__construct($fieldname, array(), $options);
    $this->symfonyFormType = CountryType::class;
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