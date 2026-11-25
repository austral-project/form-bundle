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

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Wysiwyg Field.
 *
 * Textarea enhanced by the editor of the admin: "wysiwyg_options" is its configuration (camelCase, as the
 * editor names it), given as JSON in data-wysiwyg-options.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class WysiwygField extends Field
{
  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = TextareaType::class;

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
    $resolver->setOptions('wysiwyg_options', static function (OptionsResolver $wysiwyg): void {
      $wysiwyg->setDefaults([
        'minHeight' => 250,
        'toolbar' => [
          ['actions', ['undo', 'redo']],
          ['style', ['bold', 'italic', 'underline', 'clear', 'strikethrough', 'superscript', 'subscript']],
          ['para', ['ul', 'ol', 'paragraph', 'hr']],
          ['links', ['austral-links']],
          ['options', ['codeview', 'fullscreen']],
        ],
        'popover' => [
          'link' => ['austral-links', 'unlink'],
        ],
        'autoLink' => true,
        'icons' => [
          'undo' => 'austral-picto-corner-rearward',
          'redo' => 'austral-picto-corner-forward',
          'italic' => 'austral-picto-italic',
          'bold' => 'austral-picto-bold',
          'link' => 'austral-picto-link',
          'unlink' => 'austral-picto-link-2',
          'underline' => 'austral-picto-underline',
          'strikethrough' => 'austral-picto-strike',
          'superscript' => 'austral-picto-superior',
          'orderedlist' => 'austral-picto-list-ol',
          'unorderedlist' => 'austral-picto-list-ul',
          'minus' => 'austral-picto-minus',
          'code' => 'austral-picto-code',
          'arrowsAlt' => 'austral-picto-maximize',
          'alignLeft' => 'austral-picto-align-left',
          'alignRight' => 'austral-picto-align-right',
          'alignJustify' => 'austral-picto-align-justify',
          'alignCenter' => 'austral-picto-align-center',
          'outdent' => 'austral-picto-indent-less',
          'indent' => 'austral-picto-indent-more',
        ],
      ])
        ->setAllowedTypes('minHeight', ['null', 'int'])
        ->setAllowedTypes('toolbar', 'array')
        ->setAllowedTypes('popover', 'array')
        ->setAllowedTypes('autoLink', 'bool')
        ->setAllowedTypes('icons', 'array');
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $options['attr']['data-wysiwyg'] = true;
    $options['attr']['data-wysiwyg-options'] = json_encode($this->options['wysiwyg_options'], \JSON_THROW_ON_ERROR);

    return $options;
  }
}
