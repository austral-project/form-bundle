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
 * Austral Field Symfony Input.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class SymfonyField extends Field
{

  /**
   * @param $fieldname
   * @param string $symfonyFormType
   * @param array $options
   *
   * @return $this
   */
  public static function create($fieldname, string $symfonyFormType, array $options = array()): SymfonyField
  {
    return new self($fieldname, $symfonyFormType, $options);
  }

  /**
   * SymfonyField constructor.
   *
   * @param string $fieldname
   * @param string $symfonyFormType
   * @param array $options
   */
  public function __construct(string $fieldname, string $symfonyFormType, array $options = array())
  {
    parent::__construct($fieldname, $options);
    $this->symfonyFormType = $symfonyFormType;
  }

}