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
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class WysiwygField extends Field
{

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, array $options = array()): WysiwygField
  {
    return new self($fieldname, $options);
  }

  /**
   * WysiwygField constructor.
   *
   * @param string $fieldname
   * @param array $options
   */
  public function __construct($fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = TextareaType::class;
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
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
   * @return array
   */
  public function getFieldOptions(): array
  {
    $fieldOptions = parent::getFieldOptions();
    $fieldOptions["attr"]["data-wysiwyg"] = true;
    $fieldOptions["attr"]["data-wysiwyg-options"] = json_encode($this->options['wysiwyg-options']);
    return $fieldOptions;
  }

}