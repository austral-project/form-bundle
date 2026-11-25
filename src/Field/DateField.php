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

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Date Field (the 3.x DatePicker).
 *
 * DateType as a single text input, with the date picker of the admin: "datepicker_options" is its
 * configuration (camelCase, as the picker names it), given as JSON in data-datepicker-options. Its format
 * follows "date_format" (ICU, dd/MM/yyyy) unless given.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class DateField extends Field
{
  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = DateType::class;

  /**
   * @param string               $name
   * @param array<string, mixed> $options
   *
   * @return self
   */
  public static function create(string $name, array $options = []): self
  {
    return new self($name, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults([
      'picto' => 'austral-picto-calendar',
      'date_format' => 'dd/MM/yyyy',
    ]);
    $resolver->setAllowedTypes('date_format', 'string');
    $resolver->setOptions('datepicker_options', static function (OptionsResolver $picker, Options $parent): void {
      $picker->setDefaults([
        'showWeek' => false,
        'todayBtn' => true,
        'clearBtn' => true,
        'calendarWeeks' => false,
        'autohide' => true,
        'weekStart' => 1,
        'minDate' => null,
        'maxDate' => null,
        'language' => 'en',
        'format' => str_replace('MM', 'mm', $parent['date_format']),
        'datesDisabled' => [],
        'daysOfWeekDisabled' => [],
      ])
        ->setAllowedTypes('showWeek', 'bool')
        ->setAllowedTypes('todayBtn', 'bool')
        ->setAllowedTypes('clearBtn', 'bool')
        ->setAllowedTypes('calendarWeeks', 'bool')
        ->setAllowedTypes('autohide', 'bool')
        ->setAllowedTypes('weekStart', 'int')
        ->setAllowedTypes('minDate', ['null', 'string', 'int'])
        ->setAllowedTypes('maxDate', ['null', 'string', 'int'])
        ->setAllowedTypes('language', ['null', 'string'])
        ->setAllowedTypes('format', 'string')
        ->setAllowedTypes('datesDisabled', 'array')
        ->setAllowedTypes('daysOfWeekDisabled', 'array');
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $options += [
      'widget' => 'single_text',
      'format' => $this->options['date_format'],
      'html5' => false,
    ];
    $options['attr']['data-datepicker'] = true;
    $options['attr']['data-datepicker-options'] = json_encode($this->options['datepicker_options'], \JSON_THROW_ON_ERROR);

    return $options;
  }
}
