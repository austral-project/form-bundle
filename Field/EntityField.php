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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field Entity input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class EntityField extends BaseSelectField
{

  /**
   * @var array
   */
  protected array $choices = array();

  /**
   * @var string
   */
  protected string $entityClass;

  /**
   * @param $fieldname
   * @param string $entityClass
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, string $entityClass, array $options = array()): EntityField
  {
    return new EntityField($fieldname, $entityClass, $options);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param string $entityClass
   * @param array $options
   */
  public function __construct($fieldname, string $entityClass, array $options = array())
  {
    parent::__construct($fieldname, array(), $options);
    $this->symfonyFormType = EntityType::class;
    $this->entityClass = $entityClass;
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function configureOptions(OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults(array(
      "query_builder"   =>  null,
      "choice_label"    =>  null
    ));
  }

  /**
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions["attr"]["class"] = AustralTools::getValueByKey(AustralTools::getValueByKey($fieldOptions, "attr", array()), "class")." {$this->entityClass}";
    $fieldOptions["attr"]['autocomplete'] = AustralTools::getValueByKey(AustralTools::getValueByKey($fieldOptions, "attr", array()), "autocomplete", "off");
    $fieldOptions["class"] = $this->entityClass;
    $fieldOptions["query_builder"] = array_key_exists("query_builder", $fieldOptions) ? $fieldOptions["query_builder"] : $this->options['query_builder'];
    $fieldOptions["choice_label"] = array_key_exists("choice_label", $fieldOptions) ? $fieldOptions["choice_label"] : $this->options['choice_label'];
    if(!$fieldOptions['choice_label'])
    {
      unset($fieldOptions['choice_label']);
    }
    unset($fieldOptions['choices']);
    return $fieldOptions;
  }

}