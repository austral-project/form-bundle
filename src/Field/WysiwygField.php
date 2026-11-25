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

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Field Wysiwyg Input.
 * @template T of object
 *
 * @extends Field<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class WysiwygField extends Field
{

  /**
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @return self<object>
   */
  public static function create(string $fieldname, array $options = []): self
  {
    return new self($fieldname, $options);
  }

  /**
   * AddressAutoCompleteField constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   */
  public function __construct(string $fieldname, array $options = [])
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = TextareaType::class;
  }

  /**
   * @inheritDoc
   */
  public function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('wysiwyg-options', function (OptionsResolver $resolverChild) {
      $resolverChild->setDefaults(array(
          "minHeight"     =>  250,
          "toolbar"       =>  array(
            array('actions', array('undo', 'redo')),
            array('style', array('bold', 'italic', 'underline', 'clear', 'strikethrough', 'superscript', 'subscript')),
            array('para', array('ul', 'ol', 'paragraph', 'hr')),
            array('links', array('austral-links')),
            array('options', array('codeview', 'fullscreen')),
          ),
          "popover"       =>  array(
            'link'          =>  array("austral-links", "unlink"),
          ),
          "autoLink"      =>  true,
          "icons"         =>  array(
            "undo"            =>  "austral-picto-corner-rearward",
            "redo"            =>  "austral-picto-corner-forward",
            "italic"          =>  "austral-picto-italic",
            "bold"            =>  "austral-picto-bold",
            "link"            =>  "austral-picto-link",
            "unlink"          =>  "austral-picto-link-2",
            "underline"       =>  "austral-picto-underline",
            "strikethrough"   =>  "austral-picto-strike",
            "superscript"     =>  "austral-picto-superior",
            "orderedlist"     =>  "austral-picto-list-ol",
            "unorderedlist"   =>  "austral-picto-list-ul",
            "minus"           =>  "austral-picto-minus",
            "code"            =>  "austral-picto-code",
            "arrowsAlt"       =>  "austral-picto-maximize",
            "alignLeft"       =>  "austral-picto-align-left",
            "alignRight"      =>  "austral-picto-align-right",
            "alignJustify"    =>  "austral-picto-align-justify",
            "alignCenter"     =>  "austral-picto-align-center",
            "outdent"         =>  "austral-picto-indent-less",
            "indent"          =>  "austral-picto-indent-more",
          )
        )
      );
    });
  }

  /**
   * @inheritDoc
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions["attr"]["data-wysiwyg"] = true;
    $fieldOptions["attr"]["data-wysiwyg-options"] = json_encode($this->options['wysiwyg-options']);
    return $fieldOptions;
  }

}