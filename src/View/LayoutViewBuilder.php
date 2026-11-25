<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\View;

use Austral\FormBundle\Field\Field;
use Austral\FormBundle\Layout\Fieldset;
use Austral\FormBundle\Layout\Group;
use Austral\FormBundle\Layout\LayoutNode;
use Austral\FormBundle\Layout\Modal;
use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Component\Form\FormView;

/**
 * Austral Layout View Builder.
 *
 * Builds form.vars.austral_layout, the view contract of the layout: the visible nodes in display order, each one
 * with its children (names of the fields to render with form_row(), or nodes). The ids are prefixed with the id
 * of the form view, so they are unique on the page and in every entry of a collection.
 *
 * @internal used by AustralFormType
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class LayoutViewBuilder
{
  /**
   * @param FormMapper  $mapper
   * @param FormView    $view   View of the form built from the mapper, its children already built
   * @param object|null $data   Data of the form, given to the label parameters of the fieldsets
   *
   * @return list<array<string, mixed>>
   */
  public static function build(FormMapper $mapper, FormView $view, ?object $data): array
  {
    $nodes = [];
    foreach ($mapper->getNodes() as $node)
    {
      if (null !== ($built = self::node($node, $mapper, $view, $data)))
      {
        $nodes[] = $built;
      }
    }

    return $nodes;
  }

  /**
   * @param LayoutNode  $node
   * @param FormMapper  $mapper
   * @param FormView    $view
   * @param object|null $data
   *
   * @return array<string, mixed>|null null when the node is not visible
   */
  private static function node(LayoutNode $node, FormMapper $mapper, FormView $view, ?object $data): ?array
  {
    if (!$node->isVisible())
    {
      return null;
    }
    $children = [];
    foreach ($node->getChildren() as $child)
    {
      if ($child instanceof LayoutNode)
      {
        if (null !== ($built = self::node($child, $mapper, $view, $data)))
        {
          $children[] = $built;
        }
        continue;
      }
      // A field of the form: AustralFormType only built the visible ones.
      if (isset($view->children[$child]))
      {
        $children[] = $child;
        continue;
      }
      $field = $mapper->getField($child);
      if (null !== $field && $field->isDisplayOnly() && $field->isVisible())
      {
        $children[] = self::template($field, $view);
      }
    }

    $built = [
      'type' => $node::TYPE,
      'id' => $view->vars['id'].'-'.$node->getId(),
      'name' => $node->getName(),
      'label' => $node->getLabel(),
      'attr' => $node->getAttr(),
      'children' => $children,
    ];

    return match (true) {
      $node instanceof Fieldset => $built + [
        'position' => $node->getPosition(),
        'collapse' => $node->isCollapse(),
        'show_label' => $node->isShowLabel(),
        'label_parameters' => $node->resolveLabelParameters($data),
      ],
      $node instanceof Group => $built + [
        'direction' => $node->getDirection(),
        'size' => $node->getSize(),
        'style' => $node->getStyle(),
      ],
      $node instanceof Modal => $built + [
        'fieldname' => $node->getFieldname(),
        'modal' => $node->getModalOptions(),
        'button' => $node->getButton(),
      ],
      default => $built,
    };
  }

  /**
   * Node of a display-only field (TemplateField): rendered from its template, it is not part of the form.
   *
   * @param Field    $field
   * @param FormView $view
   *
   * @return array<string, mixed>
   */
  private static function template(Field $field, FormView $view): array
  {
    return [
      'type' => 'template',
      'id' => $view->vars['id'].'-template-'.LayoutNode::slug($field->getName()),
      'name' => $field->getName(),
      'template' => $field->getTemplate(),
      'template_vars' => $field->getTemplateVars(),
      'austral' => $field->getPresentation(),
    ];
  }
}
