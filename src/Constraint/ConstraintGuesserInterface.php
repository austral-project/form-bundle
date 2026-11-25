<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Constraint;

use Austral\FormBundle\Field\Field;
use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Component\Validator\Constraint;

/**
 * Austral Constraint Guesser.
 *
 * Constraints added automatically to the fields of an AustralFormType (option "auto_constraints" of a field).
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface ConstraintGuesserInterface
{
  /**
   * Constraints of one field.
   *
   * @param class-string|null $dataClass
   * @param Field             $field
   *
   * @return list<Constraint>
   */
  public function guessFieldConstraints(?string $dataClass, Field $field): array;

  /**
   * Constraints of the form itself (UniqueEntity for example), added to the "constraints" option of the root form.
   *
   * @param class-string|null $dataClass
   * @param FormMapper        $mapper
   *
   * @return list<Constraint>
   */
  public function guessClassConstraints(?string $dataClass, FormMapper $mapper): array;
}
