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
 * @template T of object
 *
 * @extends Field<T>
 *
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
   * @param string $fieldname
   * @param string|null $size
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, ?string $size = self::SIZE_MIDDLE, array $options = []): self
  {
    return new self($fieldname, $size, $options);
  }

  /**
   * TextareaField constructor.
   *
   * @param string $fieldname
   * @param string|null $size
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, ?string $size = self::SIZE_MIDDLE, array $options = [])
  {
    parent::__construct($fieldname, $options);
    $this->size = $size ?? self::SIZE_MIDDLE;
    $this->symfonyFormType = TextareaType::class;
  }

  /**
   * @inheritDoc
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    if($this->size == self::SIZE_AUTO)
    {
      $fieldOptions['attr']['rows'] = 2;
    }
    $fieldOptions["attr"]["class"] = AustralTools::valueByKey(AustralTools::valueByKey($fieldOptions, "attr", []), "class")." {$this->size}";
    return $fieldOptions;
  }



}