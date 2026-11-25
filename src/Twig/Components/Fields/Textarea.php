<?php
/*
 * This file is part of the App package.
 *
 * (c) Yipikai <support@yipikai.studio>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Twig\Components\Fields;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent("Austral:Form:Textarea", template: '@AustralForm/Components/Fields/textarea.html.twig')]
final class Textarea extends BaseField
{

}