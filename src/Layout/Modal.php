<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Layout;

use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Modal.
 *
 * A modal opened by a button. When a field name is given, the button is shown next to this field.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class Modal extends LayoutNode
{
  public const string TYPE = 'modal';

  /**
   * @var array{modal: array{class: ?string, template: ?string}, button: array{label: ?string, picto: ?string, class: ?string, data: array<string, mixed>}}
   */
  protected array $options;

  /**
   * @param string               $name
   * @param FormMapper           $mapper
   * @param LayoutNode|null      $parent
   * @param string|null          $fieldname
   * @param array<string, mixed> $options
   */
  public function __construct(string $name, FormMapper $mapper, ?LayoutNode $parent = null, protected ?string $fieldname = null, array $options = [])
  {
    parent::__construct($name, $mapper, $parent, $options['label'] ?? null);
    unset($options['label']);
    $resolver = new OptionsResolver();
    $resolver->setOptions('modal', static function (OptionsResolver $modal): void {
      $modal->setDefaults(['class' => null, 'template' => null])
        ->setAllowedTypes('class', ['null', 'string'])
        ->setAllowedTypes('template', ['null', 'string']);
    });
    $resolver->setOptions('button', static function (OptionsResolver $button): void {
      $button->setDefaults(['label' => null, 'picto' => 'austral-picto-edit', 'class' => 'button-picto', 'data' => []])
        ->setAllowedTypes('label', ['null', 'string'])
        ->setAllowedTypes('picto', ['null', 'string'])
        ->setAllowedTypes('class', ['null', 'string'])
        ->setAllowedTypes('data', 'array');
    });
    $this->options = $resolver->resolve($options);
  }

  /**
   * @return string|null
   */
  public function getFieldname(): ?string
  {
    return $this->fieldname;
  }

  /**
   * @return array{class: ?string, template: ?string}
   */
  public function getModalOptions(): array
  {
    return $this->options['modal'];
  }

  /**
   * @return array{label: ?string, picto: ?string, class: ?string, data: array<string, mixed>}
   */
  public function getButton(): array
  {
    return $this->options['button'];
  }
}
