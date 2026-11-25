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

use Austral\FormBundle\Constraint\ConstraintGuesserInterface;
use Austral\FormBundle\Constraint\DoctrineConstraintGuesser;
use Austral\FormBundle\Field\Field;
use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Layout\Fieldset;
use Austral\FormBundle\Layout\Group;
use Austral\FormBundle\Layout\LayoutNode;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\FormBundle\Tests\Fixtures\Article;
use Austral\FormBundle\Tests\Fixtures\TemplateLikeField;
use Austral\FormBundle\Tests\Fixtures\TextLikeField;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Validation;

final class AustralFormTypeTest extends TestCase
{
  public function testMapperIsRequired(): void
  {
    $this->expectException(MissingOptionsException::class);

    $this->factory()->create(AustralFormType::class);
  }

  public function testMapperMustBeAFormMapper(): void
  {
    $this->expectException(InvalidOptionsException::class);

    $this->factory()->create(AustralFormType::class, null, ['mapper' => 'form']);
  }

  public function testNamedFormKeepsTheHtmlNames(): void
  {
    $mapper = new FormMapper('form_austral', Article::class)->add(TextLikeField::create('title'));

    $view = $this->create($mapper, new Article())->createView();

    self::assertSame('form_austral', $view->vars['full_name']);
    self::assertSame('form_austral[title]', $view['title']->vars['full_name']);
    self::assertSame('form_austral_title', $view['title']->vars['id']);
  }

  public function testBlockPrefix(): void
  {
    $view = $this->create(new FormMapper('form_austral'))->createView();

    self::assertContains(AustralFormType::BLOCK_PREFIX, $view->vars['block_prefixes']);
    self::assertSame('_form_austral', $view->vars['unique_block_prefix']);
  }

  public function testOptionsFromTheMapper(): void
  {
    $mapper = new FormMapper('form_austral', Article::class)
      ->setTranslationDomain('austral')
      ->setValidationGroups(['Default', 'admin']);

    $config = $this->create($mapper)->getConfig();

    self::assertSame(Article::class, $config->getDataClass());
    self::assertSame('austral', $config->getOption('translation_domain'));
    self::assertSame(['Default', 'admin'], $config->getOption('validation_groups'));
  }

  public function testOptionsGivenToTheFormWin(): void
  {
    $mapper = new FormMapper('form_austral', Article::class)->setTranslationDomain('austral');

    $form = $this->create($mapper, null, ['translation_domain' => 'messages', 'data_class' => null]);

    self::assertSame('messages', $form->getConfig()->getOption('translation_domain'));
    self::assertNull($form->getConfig()->getDataClass());
  }

  public function testNoValidateUnlessHtml5Validation(): void
  {
    $mapper = new FormMapper('form_austral');
    self::assertSame(['class' => 'form', 'novalidate' => 'novalidate'], $this->create($mapper, null, ['attr' => ['class' => 'form']])->createView()->vars['attr']);

    $mapper->setHtml5Validate(true);
    self::assertSame([], $this->create($mapper)->createView()->vars['attr']);
  }

  public function testFieldsAddedWithTheirSymfonyOptions(): void
  {
    $mapper = new FormMapper('form_austral', Article::class)
      ->add(TextLikeField::create('title', ['label' => 'Title', 'required' => true, 'attr' => ['maxlength' => 120]]));

    $title = $this->create($mapper)->get('title')->getConfig();

    self::assertInstanceOf(TextType::class, $title->getType()->getInnerType());
    self::assertSame('Title', $title->getOption('label'));
    self::assertTrue($title->getOption('required'));
    self::assertSame(['maxlength' => 120], $title->getOption('attr'));
    self::assertSame([NotNull::class], self::classes($title->getOption('constraints')));
  }

  public function testPresentationInTheViewOfEachField(): void
  {
    $mapper = new FormMapper('form_austral')->add(TextLikeField::create('title', ['picto' => 'austral-picto-text', 'size' => 'col--6']));

    $austral = $this->create($mapper)->createView()['title']->vars['austral'];

    self::assertSame('austral-picto-text', $austral['picto']);
    self::assertSame('col--6', $austral['size']);
    self::assertSame('textLikeField', $austral['field_class']);
    self::assertSame(array_keys(PresentationTypeExtension::DEFAULTS), array_keys($austral));
  }

  public function testFieldsAddedInLayoutOrder(): void
  {
    $mapper = new FormMapper('form_austral');
    $mapper->addFieldset('second')->add(TextLikeField::create('c'))->setOrder(2);
    $mapper->addFieldset('first')->add(TextLikeField::create('b'))->add(TextLikeField::create('a'), -1)->setOrder(1);

    self::assertSame(['a', 'b', 'c'], array_keys($this->create($mapper)->all()));
  }

  public function testDisplayOnlyFieldIsNotPartOfTheForm(): void
  {
    $mapper = new FormMapper('form_austral')
      ->add(TextLikeField::create('title'))
      ->add(TemplateLikeField::create('information', '@AustralForm/information.html.twig'));

    self::assertSame(['title'], array_keys($this->create($mapper)->all()));
  }

  public function testInvisibleFieldIsNotPartOfTheForm(): void
  {
    $mapper = new FormMapper('form_austral')
      ->add(TextLikeField::create('title'))
      ->add(TextLikeField::create('slug', ['visible' => static fn (): bool => false]));

    self::assertSame(['title'], array_keys($this->create($mapper)->all()));
  }

  /**
   * An invisible field can be neither rendered nor submitted: its data stays as it is.
   */
  public function testInvisibleFieldKeepsItsData(): void
  {
    $mapper = new FormMapper('form_austral', Article::class)
      ->add(TextLikeField::create('title'))
      ->add(TextLikeField::create('slug', ['visible' => false]));
    $article = new Article();
    $article->slug = 'kept';

    $this->create($mapper, $article)->submit(['title' => 'Hello', 'slug' => 'changed']);

    self::assertSame('Hello', $article->title);
    self::assertSame('kept', $article->slug);
  }

  public function testFieldsOfAnInvisibleNodeAreNotPartOfTheForm(): void
  {
    $mapper = new FormMapper('form_austral');
    $mapper->addFieldset('main')->add(TextLikeField::create('title'))
      ->addGroup('dev')->setVisible(false)->add(TextLikeField::create('slug'));
    $mapper->addFieldset('seo')->setVisible(static fn (LayoutNode $node): bool => false)->add(TextLikeField::create('summary'));

    self::assertSame(['title'], array_keys($this->create($mapper)->all()));
  }

  public function testFieldWithoutSymfonyType(): void
  {
    $mapper = new FormMapper('form_austral')->add(new class('title') extends Field {});

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('The field "title"');

    $this->create($mapper);
  }

  public function testGuessedFieldConstraintsAdded(): void
  {
    $mapper = new FormMapper('form_austral', Article::class)->add(TextLikeField::create('title'));
    $guesser = $this->guesser(field: [new Length(max: 120)]);

    $title = $this->create($mapper, null, [], $guesser)->get('title');

    self::assertSame([Length::class], self::classes($title->getConfig()->getOption('constraints')));
    self::assertSame([[Article::class, 'title']], $guesser->fieldCalls);
  }

  public function testGuessedConstraintDoesNotReplaceTheOneOfTheField(): void
  {
    $field = TextLikeField::create('title')->addConstraint(new Length(max: 50));
    $mapper = new FormMapper('form_austral', Article::class)->add($field);

    $constraints = $this->create($mapper, null, [], $this->guesser(field: [new Length(max: 120)]))->get('title')->getConfig()->getOption('constraints');

    self::assertCount(1, $constraints);
    self::assertInstanceOf(Length::class, $constraints[0]);
    self::assertSame(50, $constraints[0]->max);
  }

  public function testBuildingTheFormTwiceAddsNothing(): void
  {
    $mapper = new FormMapper('form_austral', Article::class)->add(TextLikeField::create('title', ['required' => true]));
    $guesser = $this->guesser(field: [new Length(max: 120), new NotNull()]);

    $this->create($mapper, null, [], $guesser);
    $title = $this->create($mapper, null, [], $guesser)->get('title');

    self::assertSame([Length::class, NotNull::class], self::classes($title->getConfig()->getOption('constraints')));
  }

  public function testGuessedNotNullMakesTheFieldRequired(): void
  {
    $mapper = new FormMapper('form_austral', Article::class)->add(TextLikeField::create('title'));

    $title = $this->create($mapper, null, [], $this->guesser(field: [new NotNull()]))->get('title');

    self::assertTrue($title->getConfig()->getOption('required'));
    self::assertTrue($mapper->getField('title')?->isRequired());
  }

  public function testGuessedClassConstraintsAddedToTheRootForm(): void
  {
    $mapper = new FormMapper('form_austral', Article::class);
    $guesser = $this->guesser(class: [new NotBlank()]);

    $constraints = $this->create($mapper, null, ['constraints' => new NotNull()], $guesser)->getConfig()->getOption('constraints');

    self::assertSame([NotNull::class, NotBlank::class], self::classes($constraints));
  }

  public function testSubmitAndValidation(): void
  {
    $mapper = new FormMapper('form_austral', Article::class)
      ->add(TextLikeField::create('title', ['required' => true]))
      ->add(TextLikeField::create('summary'));
    $article = new Article();

    $form = $this->create($mapper, $article);
    $form->submit(['title' => '', 'summary' => 'Text']);

    self::assertTrue($form->isSynchronized());
    self::assertFalse($form->isValid());
    self::assertSame('Text', $article->summary);
    self::assertSame('errors.not_null', $form->get('title')->getErrors()[0]->getMessageTemplate());

    $form = $this->create($mapper, $article);
    $form->submit(['title' => 'Hello', 'summary' => 'Text']);
    self::assertTrue($form->isValid());
    self::assertSame('Hello', $article->title);
  }

  public function testLayoutContract(): void
  {
    $mapper = new FormMapper('form_austral', Article::class);
    $mapper->addFieldset('General information', 'fieldsets.general')
      ->setCollapse()
      ->setLabelParameters(['%type%' => 'article'])
      ->setLabelParametersResolver(static fn (Fieldset $fieldset, ?object $data): array => ['%title%' => $data instanceof Article ? $data->title : null])
      ->add(TextLikeField::create('title'))
      ->addGroup('Dates', false)
        ->setDirection(Group::DIRECTION_COLUMN)
        ->setSize(Group::SIZE_COL_6)
        ->add(TextLikeField::create('publishedAt'))
        ->end()
      ->add(TemplateLikeField::create('information', 'information.html.twig', ['template_vars' => ['level' => 'info']]))
      ->addModal('Link editor', 'summary', ['button' => ['label' => 'Edit']])
        ->add(TextLikeField::create('summary'));
    $mapper->addFieldset('hidden')->setVisible(false)->add(TextLikeField::create('slug'));
    $article = new Article();
    $article->title = 'Hello';

    $layout = $this->create($mapper, $article)->createView()->vars['austral_layout'];

    self::assertCount(1, $layout);
    [$fieldset] = $layout;
    self::assertSame([
      'type' => 'fieldset',
      'id' => 'form_austral-fieldset-general-information',
      'name' => 'General information',
      'label' => 'fieldsets.general',
      'attr' => [],
      'position' => Fieldset::POSITION_MASTER,
      'collapse' => true,
      'show_label' => true,
      'label_parameters' => ['%type%' => 'article', '%title%' => 'Hello'],
    ], array_diff_key($fieldset, ['children' => true]));

    [$title, $group, $template, $modal] = $fieldset['children'];
    self::assertSame('title', $title);
    self::assertSame([
      'type' => 'group',
      'id' => 'form_austral-group-dates',
      'name' => 'Dates',
      'label' => false,
      'attr' => [],
      'children' => ['publishedAt'],
      'direction' => Group::DIRECTION_COLUMN,
      'size' => Group::SIZE_COL_6,
      'style' => Group::STYLE_NONE,
    ], $group);
    self::assertIsArray($template);
    self::assertSame('template', $template['type']);
    self::assertSame('form_austral-template-information', $template['id']);
    self::assertSame('information.html.twig', $template['template']);
    self::assertSame(['level' => 'info'], $template['template_vars']);
    self::assertSame('templateLikeField', $template['austral']['field_class']);
    self::assertIsArray($modal);
    self::assertSame('modal', $modal['type']);
    self::assertSame('form_austral-modal-link-editor', $modal['id']);
    self::assertSame('summary', $modal['fieldname']);
    self::assertSame(['summary'], $modal['children']);
    self::assertSame('Edit', $modal['button']['label']);
    self::assertSame('austral-picto-edit', $modal['button']['picto']);
  }

  public function testLayoutSkipsInvisibleFields(): void
  {
    $mapper = new FormMapper('form_austral')
      ->add(TextLikeField::create('title'))
      ->add(TextLikeField::create('slug', ['visible' => false]))
      ->add(TemplateLikeField::create('information', 'information.html.twig', ['visible' => false]));

    $layout = $this->create($mapper)->createView()->vars['austral_layout'];

    self::assertSame('form_austral-fieldset-default', $layout[0]['id']);
    self::assertFalse($layout[0]['label']);
    self::assertFalse($layout[0]['show_label']);
    self::assertSame(['title'], $layout[0]['children']);
  }

  public function testLayoutIdsFollowTheNameOfTheForm(): void
  {
    $mapper = new FormMapper('form_austral')->add(TextLikeField::create('title'));

    $view = $this->factory()->createNamed('seo_url_12', AustralFormType::class, null, ['mapper' => $mapper])->createView();

    self::assertSame('seo_url_12-fieldset-default', $view->vars['austral_layout'][0]['id']);
  }

  /**
   * @param FormMapper                      $mapper
   * @param object|null                     $data
   * @param array<string, mixed>            $options
   * @param ConstraintGuesserInterface|null $guesser
   *
   * @return FormInterface<mixed>
   */
  private function create(FormMapper $mapper, ?object $data = null, array $options = [], ?ConstraintGuesserInterface $guesser = null): FormInterface
  {
    return $this->factory($guesser)->createNamed($mapper->getName(), AustralFormType::class, $data, ['mapper' => $mapper] + $options);
  }

  /**
   * @param ConstraintGuesserInterface|null $guesser
   *
   * @return FormFactoryInterface
   */
  private function factory(?ConstraintGuesserInterface $guesser = null): FormFactoryInterface
  {
    return Forms::createFormFactoryBuilder()
      ->addExtension(new ValidatorExtension(Validation::createValidator()))
      ->addType(new AustralFormType($guesser ?? new DoctrineConstraintGuesser()))
      ->addTypeExtension(new PresentationTypeExtension())
      ->getFormFactory();
  }

  /**
   * Guesser that returns the given constraints and records its calls.
   *
   * @param list<Constraint> $field
   * @param list<Constraint> $class
   *
   * @return ConstraintGuesserInterface&object{fieldCalls: list<array{string|null, string}>}
   */
  private function guesser(array $field = [], array $class = []): ConstraintGuesserInterface
  {
    return new class($field, $class) implements ConstraintGuesserInterface {
      /**
       * @var list<array{string|null, string}>
       */
      public array $fieldCalls = [];

      /**
       * @param list<Constraint> $field
       * @param list<Constraint> $class
       */
      public function __construct(
        private readonly array $field,
        private readonly array $class,
      ) {
      }

      public function guessFieldConstraints(?string $dataClass, Field $field): array
      {
        $this->fieldCalls[] = [$dataClass, $field->getName()];

        return $this->field;
      }

      public function guessClassConstraints(?string $dataClass, FormMapper $mapper): array
      {
        return $this->class;
      }
    };
  }

  /**
   * @param mixed $constraints
   *
   * @return list<string>
   */
  private static function classes(mixed $constraints): array
  {
    return array_map(static fn (object $constraint): string => $constraint::class, (array) $constraints);
  }
}
