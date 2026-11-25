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
 * @template T of object
 *
 * @extends BaseSelectField<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class EntityField extends BaseSelectField
{

  /**
   * @var array<string, mixed>
   */
  protected array $choices = [];

  /**
   * @var string
   */
  protected string $entityClass;

  /**
   * @param string $fieldname
   * @param string $entityClass
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, string $entityClass, array $options = []): self
  {
    return new self($fieldname, $entityClass, $options);
  }

  /**
   * EntityField constructor.
   *
   * @param string $fieldname
   * @param string $entityClass
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, string $entityClass, array $options = [])
  {
    parent::__construct($fieldname, [], $options);
    $this->symfonyFormType = EntityType::class;
    $this->entityClass = $entityClass;
  }

  /**
   * @inheritDoc
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults(array(
      "query_builder"   =>  null,
      "choice_label"    =>  null
    ));
  }

  /**
   * @inheritDoc
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions["attr"]["class"] = AustralTools::valueByKey(AustralTools::valueByKey($fieldOptions, "attr", []), "class")." {$this->entityClass}";
    $fieldOptions["attr"]['autocomplete'] = AustralTools::valueByKey(AustralTools::valueByKey($fieldOptions, "attr", []), "autocomplete", "off");
    $fieldOptions["class"] = $this->entityClass;
    $fieldOptions["query_builder"] = AustralTools::valueByKey($fieldOptions, "query_builder", $this->options['query_builder']);
    $fieldOptions["choice_label"] = AustralTools::valueByKey($fieldOptions, "choice_label", $this->options['choice_label']);
    if(!$fieldOptions['choice_label'])
    {
      unset($fieldOptions['choice_label']);
    }
    unset($fieldOptions['choices']);
    return $fieldOptions;
  }

}