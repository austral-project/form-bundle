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

/**
 * Austral Field Template.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class TemplateField extends Field
{

  /**
   * @var array 
   */
  protected array $vars = array();

  /**
   * @param $fieldname
   * @param string $templatePath
   * @param array $options
   * @param array $vars
   *
   * @return $this
   */
  public static function create($fieldname, string $templatePath, array $options = array(), array $vars = array()): TemplateField
  {
    return new self($fieldname, $templatePath, $options, $vars);
  }

  /**
   * Choices constructor.
   *
   * @param string $fieldname
   * @param string $templatePath
   * @param array $options
   * @param array $vars
   */
  public function __construct($fieldname, string $templatePath, array $options = array(), array $vars = array())
  {
    parent::__construct($fieldname, $options);
    $this->options['templatePath'] = $templatePath;
    $this->isDefaultTemplate = false;
    $this->symfonyFormType = "template";
    $this->usedGeneratedForm = false;
    $this->vars = $vars;
  }

  /**
   * @return array
   */
  public function getVars(): array
  {
    return $this->vars;
  }

  /**
   * @param array $vars
   *
   * @return $this
   */
  public function setVars(array $vars): TemplateField
  {
    $this->vars = $vars;
    return $this;
  }
  
}