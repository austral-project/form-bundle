<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Tests\Theme;

use Austral\FormBundle\Constraint\DoctrineConstraintGuesser;
use Austral\FormBundle\Field\CollectionEmbedField;
use Austral\FormBundle\Field\SymfonyField;
use Austral\FormBundle\Field\TemplateField;
use Austral\FormBundle\Field\TextField;
use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Layout\Fieldset;
use Austral\FormBundle\Layout\Group;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\Gallery;
use Austral\FormBundle\Tests\Fixtures\GalleryItem;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * The minimal theme rendered with Twig: the view contract from end to end.
 */
final class ThemeTest extends TestCase
{
  public function testLayout(): void
  {
    $mapper = new FormMapper('form_austral', Gallery::class);
    $mapper->addFieldset('General', 'fieldsets.general')
      ->setLabelParameters(['%name%' => 'gallery'])
      ->add(TextField::create('title', ['picto' => 'austral-picto-text', 'helper' => 'helpers.title', 'size' => 'col--6', 'class' => 'large']))
      ->addGroup('Dates', 'groups.dates')
        ->setDirection(Group::DIRECTION_COLUMN)
        ->add(SymfonyField::create('reference', HiddenType::class, ['mapped' => false]))
        ->end()
      ->add(TemplateField::create('information', 'information.html.twig', [], ['level' => 'info']));
    $mapper->addFieldset('Hidden')->setVisible(false)->add(TextField::create('secret', ['mapped' => false]));

    $xpath = $this->render($this->form($mapper, new Gallery()));

    $fieldset = $xpath->query('//fieldset[@id="form_austral-fieldset-general"]')->item(0);
    self::assertInstanceOf(\DOMElement::class, $fieldset);
    self::assertSame('austral-fieldset austral-fieldset--master', $fieldset->getAttribute('class'));
    self::assertSame('fieldsets.general', $xpath->evaluate('string(legend)', $fieldset));
    self::assertSame(1, $xpath->query('.//div[@class="austral-row textField col--6"]//input[@name="form_austral[title]"]', $fieldset)->length);
    self::assertSame('large', $this->attribute($xpath, '//input[@name="form_austral[title]"]', 'class'));
    self::assertSame(1, $xpath->query('//span[@class="austral-picto austral-picto-text"]')->length);
    self::assertSame('helpers.title', $xpath->evaluate('string(//p[@class="austral-helper"])'));
    self::assertSame(1, $xpath->query('//div[@id="form_austral-group-dates"][@class="austral-group group-column auto"]//input[@type="hidden"][@name="form_austral[reference]"]')->length);
    self::assertSame('info', $this->attribute($xpath, '//aside[@class="information"]', 'data-level'));
    self::assertSame(0, $xpath->query('//*[@id="form_austral-fieldset-hidden"]')->length);
    self::assertSame(0, $xpath->query('//input[@name="form_austral[secret]"]')->length);
  }

  public function testNoValidateOnTheForm(): void
  {
    $xpath = $this->render($this->form(new FormMapper('form_austral')->add(TextField::create('title')), null));

    self::assertSame('novalidate', $this->attribute($xpath, '//form', 'novalidate'));
    self::assertSame(0, $xpath->query('//div[@novalidate]')->length);
  }

  public function testSlots(): void
  {
    $mapper = new FormMapper('form_austral')->add(TextField::create('title', [
      'before' => '<strong class="slot-html">Before</strong>',
      'after' => ['template' => 'slot.html.twig', 'parameters' => ['text' => 'After']],
    ]));

    $xpath = $this->render($this->form($mapper, null));

    self::assertSame('Before', $xpath->evaluate('string(//div[@class="austral-slot austral-slot--before"]/strong[@class="slot-html"])'));
    self::assertSame('After', $xpath->evaluate('string(//div[@class="austral-slot austral-slot--after"]/em[@class="slot-template"])'));
  }

  public function testModal(): void
  {
    $mapper = new FormMapper('form_austral');
    $mapper->addFieldset('main')->addModal('Link editor', 'url', ['label' => 'modals.link', 'button' => ['data' => ['target' => 'url']]])
      ->add(TextField::create('url'));

    $xpath = $this->render($this->form($mapper, null));

    self::assertSame('form_austral-modal-link-editor', $this->attribute($xpath, '//button[@data-austral-modal]', 'data-austral-modal'));
    self::assertSame('url', $this->attribute($xpath, '//button[@data-austral-modal]', 'data-target'));
    self::assertSame('modals.link', $xpath->evaluate('string(//button[@data-austral-modal])'));
    self::assertSame(1, $xpath->query('//dialog[@id="form_austral-modal-link-editor"]//input[@name="form_austral[url]"]')->length);
  }

  public function testCollectionEntriesAndPrototype(): void
  {
    $items = new FormMapper('item', GalleryItem::class);
    $items->addFieldset('item', 'fieldsets.item')
      ->setLabelParametersResolver(static fn (Fieldset $fieldset, ?object $item): array => ['%label%' => $item instanceof GalleryItem ? $item->label : ''])
      ->add(TextField::create('label'));
    $mapper = new FormMapper('form_austral', Gallery::class)->add(CollectionEmbedField::create('items', ['entry' => ['mapper' => $items]]));
    $gallery = new Gallery();
    $gallery->items = [new GalleryItem('First'), new GalleryItem('Second')];

    $xpath = $this->render($this->form($mapper, $gallery));

    self::assertSame(1, $xpath->query('//fieldset[@id="form_austral_items_0-fieldset-item"]//input[@name="form_austral[items][0][label]"]')->length);
    self::assertSame(1, $xpath->query('//fieldset[@id="form_austral_items_1-fieldset-item"]//input[@name="form_austral[items][1][label]"]')->length);
    $prototype = $this->attribute($xpath, '//*[@data-prototype]', 'data-prototype');
    self::assertStringContainsString('id="form_austral_items___name__-fieldset-item"', $prototype);
    self::assertStringContainsString('name="form_austral[items][__name__][label]"', $prototype);
  }

  public function testVariants(): void
  {
    $variant = static fn (string $type): CollectionEmbedField => CollectionEmbedField::create("items_{$type}", [
      'entry' => ['mapper' => new FormMapper($type, GalleryItem::class)->add(TextField::create('label'))],
      'getter' => static fn (Gallery $gallery): array => $gallery->getItemsByType($type),
      'setter' => static function (Gallery $gallery, array $items) use ($type): void {
        $gallery->setItemsByType($type, $items);
      },
    ]);
    $mapper = new FormMapper('form_austral', Gallery::class)->add(CollectionEmbedField::create('items')->addVariant('image', $variant('image'))->addVariant('text', $variant('text')));
    $gallery = new Gallery();
    $gallery->items = [new GalleryItem('Photo', 1, 'image')];

    $xpath = $this->render($this->form($mapper, $gallery));

    self::assertSame(1, $xpath->query('//div[@id="form_austral-variants-items"]//input[@name="form_austral[items_image][0][label]"]')->length);
    self::assertSame(1, $xpath->query('//div[@id="form_austral-variants-items"]//*[@id="form_austral_items_text"]')->length);
  }

  /**
   * @param FormMapper  $mapper
   * @param object|null $data
   *
   * @return FormInterface<mixed>
   */
  private function form(FormMapper $mapper, ?object $data): FormInterface
  {
    return Forms::createFormFactoryBuilder()
      ->addExtension(new ValidatorExtension(Validation::createValidator()))
      ->addType(new AustralFormType(new DoctrineConstraintGuesser()))
      ->addTypeExtension(new PresentationTypeExtension())
      ->getFormFactory()
      ->createNamed($mapper->getName(), AustralFormType::class, $data, ['mapper' => $mapper]);
  }

  /**
   * @param FormInterface<mixed> $form
   *
   * @return \DOMXPath
   */
  private function render(FormInterface $form): \DOMXPath
  {
    $bridge = \dirname((string) new \ReflectionClass(AppVariable::class)->getFileName());
    $twig = new Environment(new FilesystemLoader([
      $bridge.'/Resources/views/Form',
      __DIR__.'/../../src/Resources/views/form',
      __DIR__.'/../Fixtures/templates',
    ]), ['strict_variables' => true]);
    $twig->addExtension(new FormExtension());
    $twig->addExtension(new TranslationExtension());
    $engine = new TwigRendererEngine(['austral_layout.html.twig'], $twig);
    $twig->addRuntimeLoader(new FactoryRuntimeLoader([FormRenderer::class => static fn (): FormRenderer => new FormRenderer($engine)]));

    $html = $twig->createTemplate('{{ form(form) }}')->render(['form' => $form->createView()]);
    $document = new \DOMDocument();
    libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>');
    libxml_clear_errors();

    return new \DOMXPath($document);
  }

  /**
   * @param \DOMXPath $xpath
   * @param string    $query
   * @param string    $attribute
   *
   * @return string
   */
  private function attribute(\DOMXPath $xpath, string $query, string $attribute): string
  {
    $node = $xpath->query($query)->item(0);

    return $node instanceof \DOMElement ? $node->getAttribute($attribute) : '';
  }
}
