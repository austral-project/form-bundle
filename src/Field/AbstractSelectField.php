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

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Abstract Select Field.
 *
 * Base of the fields shown as a select (SelectField, EntityField), enhanced by Choices.js unless
 * select_options.enabled is false. The keys of "select_options" are the configuration of Choices.js
 * (camelCase, as Choices.js names them), plus "enabled" and "tag" read by the Austral script; they are
 * given to the script as JSON in the attribute data-select-options.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
abstract class AbstractSelectField extends Field
{
  public const string THEME_DEFAULT = 'default';
  public const string THEME_CHOICE_LIST = 'choiceList';
  public const string THEME_CHOICE_ELEMENT = 'choiceElement';

  public const string DIRECTION_VERTICAL = 'vertical';
  public const string DIRECTION_HORIZONTAL = 'horizontal';

  /**
   * {@inheritdoc}
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults([
      'multiple' => false,
      'null_value' => false,
    ]);
    $resolver->setAllowedTypes('multiple', 'bool')
      ->setAllowedTypes('null_value', 'bool');
    $resolver->setOptions('theme', static function (OptionsResolver $theme): void {
      $theme->setDefaults(['name' => self::THEME_DEFAULT, 'direction' => null])
        ->setAllowedValues('name', [self::THEME_DEFAULT, self::THEME_CHOICE_LIST, self::THEME_CHOICE_ELEMENT])
        ->setAllowedValues('direction', [null, self::DIRECTION_VERTICAL, self::DIRECTION_HORIZONTAL]);
    });
    $resolver->setOptions('select_options', static function (OptionsResolver $select): void {
      $select->setDefaults([
        'enabled' => true,
        'tag' => false,
        'addItems' => true,
        'editItems' => true,
        'removeItems' => true,
        'searchEnabled' => true,
        'searchResultLimit' => 10,
        'placeholder' => true,
        'placeholderValue' => null,
        'searchPlaceholderValue' => null,
        'delimiter' => ', ',
        'duplicateItemsAllowed' => false,
        'shouldSort' => false,
      ])
        ->setAllowedTypes('enabled', 'bool')
        ->setAllowedTypes('tag', 'bool')
        ->setAllowedTypes('addItems', 'bool')
        ->setAllowedTypes('editItems', 'bool')
        ->setAllowedTypes('removeItems', 'bool')
        ->setAllowedTypes('searchEnabled', 'bool')
        ->setAllowedTypes('searchResultLimit', 'int')
        ->setAllowedTypes('placeholder', 'bool')
        ->setAllowedTypes('placeholderValue', ['null', 'string'])
        ->setAllowedTypes('searchPlaceholderValue', ['null', 'string'])
        ->setAllowedTypes('delimiter', 'string')
        ->setAllowedTypes('duplicateItemsAllowed', 'bool')
        ->setAllowedTypes('shouldSort', 'bool');
    });
  }

  /**
   * @return bool
   */
  public function isMultiple(): bool
  {
    return $this->options['multiple'];
  }

  /**
   * Configuration given to Choices.js (removeItemButton when the select is multiple, as in 3.x).
   *
   * @return array<string, mixed>
   */
  public function getSelectOptions(): array
  {
    return $this->options['select_options'] + ($this->options['multiple'] ? ['removeItemButton' => true] : []);
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $options += ['multiple' => $this->options['multiple']];
    $options['attr'] += ['autocomplete' => 'off'];
    if ($this->options['null_value'])
    {
      $options['attr']['data-null-value'] = 'true';
    }
    if (true !== ($options['expanded'] ?? false))
    {
      $options['attr']['data-select'] = $this->options['select_options']['enabled'];
      if ($this->options['select_options']['enabled'])
      {
        $options['attr']['data-select-options'] = json_encode($this->getSelectOptions(), \JSON_THROW_ON_ERROR);
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getWidget(): array
  {
    return [
      'multiple' => $this->options['multiple'],
      'tags' => $this->options['select_options']['tag'],
      'theme' => $this->options['theme']['name'],
      'direction' => $this->options['theme']['direction'],
    ];
  }
}
