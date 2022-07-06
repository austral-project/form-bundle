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
use Austral\ToolsBundle\AustralTools;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Austral Field Textarea Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class TextareaField extends Field
{

  const SIZE_MIDDLE = "middle";
  const SIZE_AUTO = "auto";
  const SIZE_BIG = "big";

  /**
   * @var string
   */
  protected string $size = self::SIZE_MIDDLE;

  /**
   * @param $fieldname
   * @param string|null $size
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, ?string $size = self::SIZE_MIDDLE, array $options = array()): TextareaField
  {
    return new self($fieldname, $size, $options);
  }

  /**
   * TextareaField constructor.
   *
   * @param string $fieldname
   * @param string|null $size
   * @param array $options
   */
  public function __construct($fieldname, ?string $size = self::SIZE_MIDDLE, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->size = $size ?? self::SIZE_MIDDLE;
    $this->symfonyFormType = TextareaType::class;
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    if($this->size == self::SIZE_AUTO)
    {
      $fieldOptions['attr']['rows'] = 2;
    }
    $fieldOptions["attr"]["class"] = AustralTools::getValueByKey(AustralTools::getValueByKey($fieldOptions, "attr", array()), "class")." {$this->size}";
    return $fieldOptions;
  }



}