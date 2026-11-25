<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Form;

use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Tests\Fixtures\TextLikeField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

final class PresentationTypeExtensionTest extends TestCase
{
  public function testNullByDefault(): void
  {
    $view = $this->factory()->createNamed('title', TextType::class)->createView();

    self::assertArrayHasKey('austral', $view->vars);
    self::assertNull($view->vars['austral']);
  }

  public function testCompletedWithTheDefaults(): void
  {
    $view = $this->factory()->createNamed('title', TextType::class, null, ['austral' => ['picto' => 'austral-picto-text']])->createView();

    self::assertSame(['picto' => 'austral-picto-text'] + PresentationTypeExtension::DEFAULTS, $view->vars['austral']);
    self::assertSame('external', $view->vars['austral']['label_position']);
  }

  public function testUnknownKeyRejected(): void
  {
    $this->expectException(InvalidOptionsException::class);
    $this->expectExceptionMessage('"pictogram"');

    $this->factory()->createNamed('title', TextType::class, null, ['austral' => ['pictogram' => 'x']]);
  }

  /**
   * Field::getPresentation() and the extension describe the same contract.
   */
  public function testSameKeysAsTheFieldPresentation(): void
  {
    self::assertSame(array_keys(PresentationTypeExtension::DEFAULTS), array_keys(TextLikeField::create('title')->getPresentation()));
  }

  /**
   * @return FormFactoryInterface
   */
  private function factory(): FormFactoryInterface
  {
    return Forms::createFormFactoryBuilder()->addTypeExtension(new PresentationTypeExtension())->getFormFactory();
  }
}
