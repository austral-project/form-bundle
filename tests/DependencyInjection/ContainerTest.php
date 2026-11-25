<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Austral\FormBundle\Tests\DependencyInjection;

use Austral\FormBundle\Constraint\ConstraintGuesserInterface;
use Austral\FormBundle\Constraint\DoctrineConstraintGuesser;
use Austral\FormBundle\DependencyInjection\AustralFormExtension;
use Austral\FormBundle\Form\Extension\PresentationTypeExtension;
use Austral\FormBundle\Form\Type\AustralFormType;
use Austral\FormBundle\Handler\FormHandler;
use Austral\FormBundle\Mapper\FormMapperFactory;
use Austral\FormBundle\Validator\RecaptchaValidator;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The services are defined explicitly: a missing or misnamed argument only shows up when the container
 * is compiled or the service is created. This test loads the extension, compiles the container and
 * creates every service of the bundle.
 */
final class ContainerTest extends TestCase
{
  /**
   * Public aliases: the supported way to get a service from the container. The form type, the type extension
   * and the validator are found by the form and validator registries through their tags, never fetched.
   */
  private const array PUBLIC_ALIASES = [
    'austral.form.constraint_guesser' => DoctrineConstraintGuesser::class,
    'austral.form.handler' => FormHandler::class,
    'austral.form.mapper_factory' => FormMapperFactory::class,
  ];

  /**
   * @return iterable<string, array{class-string}>
   */
  public static function serviceIds(): iterable
  {
    foreach (self::bundleServices() as $class)
    {
      yield $class => [$class];
    }
  }

  /**
   * @param class-string $class
   */
  #[DataProvider('serviceIds')]
  public function testEveryServiceCanBeCreated(string $class): void
  {
    self::assertInstanceOf($class, $this->compiledContainer()->get($class));
  }

  public function testTheBundleDefinesNoOtherService(): void
  {
    $ids = [];
    foreach (self::loadExtension()->getDefinitions() as $id => $definition)
    {
      if (str_starts_with($id, 'Austral\\FormBundle\\'))
      {
        $ids[] = $id;
      }
    }
    sort($ids);

    $expected = self::bundleServices();
    sort($expected);

    self::assertSame($expected, $ids);
  }

  public function testOnlyTheDocumentedAliasesArePublic(): void
  {
    $container = self::loadExtension();

    foreach ($container->getDefinitions() as $id => $definition)
    {
      if (str_starts_with($id, 'Austral\\FormBundle\\'))
      {
        self::assertFalse($definition->isPublic(), \sprintf('The service "%s" must be private.', $id));
        self::assertFalse($definition->isAutowired(), \sprintf('The service "%s" must not be autowired.', $id));
        self::assertFalse($definition->isAutoconfigured(), \sprintf('The service "%s" must not be autoconfigured.', $id));
      }
    }

    $aliases = [];
    foreach ($container->getAliases() as $id => $alias)
    {
      if (str_starts_with($id, 'austral.form'))
      {
        $aliases[$id] = (string) $alias;
        self::assertTrue($alias->isPublic(), \sprintf('The alias "%s" must be public.', $id));
      }
    }
    ksort($aliases);

    self::assertSame(self::PUBLIC_ALIASES, $aliases);
  }

  public function testTheGuesserInterfaceIsAnAlias(): void
  {
    $container = self::loadExtension();

    self::assertTrue($container->hasAlias(ConstraintGuesserInterface::class));
    self::assertSame(DoctrineConstraintGuesser::class, (string) $container->getAlias(ConstraintGuesserInterface::class));
  }

  public function testTheFormTypeAndItsExtensionAreTagged(): void
  {
    $container = self::loadExtension();

    self::assertSame([[]], $container->getDefinition(AustralFormType::class)->getTag('form.type'));
    self::assertSame([[]], $container->getDefinition(PresentationTypeExtension::class)->getTag('form.type_extension'));
  }

  public function testTheValidatorIsTagged(): void
  {
    self::assertSame([[]], self::loadExtension()->getDefinition(RecaptchaValidator::class)->getTag('validator.constraint_validator'));
  }

  public function testTheFormTypeDispatchesTheFieldEvents(): void
  {
    $formType = $this->compiledContainer()->get(AustralFormType::class);

    self::assertInstanceOf(EventDispatcher::class, new \ReflectionProperty(AustralFormType::class, 'dispatcher')->getValue($formType));
  }

  /**
   * Doctrine and the translator are optional: without them, the guesser only applies the "required" rule
   * and the reasons of reCAPTCHA are not translated.
   */
  public function testWithoutDoctrineNorTranslator(): void
  {
    $container = self::loadExtension(false);
    self::makePublic($container);
    $container->compile();

    $guesser = $container->get(DoctrineConstraintGuesser::class);
    self::assertInstanceOf(DoctrineConstraintGuesser::class, $guesser);
    self::assertNull(new \ReflectionProperty(DoctrineConstraintGuesser::class, 'registry')->getValue($guesser));
    self::assertInstanceOf(RecaptchaValidator::class, $container->get(RecaptchaValidator::class));
  }

  public function testWithDoctrine(): void
  {
    $guesser = $this->compiledContainer()->get(DoctrineConstraintGuesser::class);

    self::assertInstanceOf(ManagerRegistry::class, new \ReflectionProperty(DoctrineConstraintGuesser::class, 'registry')->getValue($guesser));
  }

  /**
   * @return list<class-string>
   */
  private static function bundleServices(): array
  {
    return [
      DoctrineConstraintGuesser::class,
      AustralFormType::class,
      PresentationTypeExtension::class,
      FormMapperFactory::class,
      FormHandler::class,
      RecaptchaValidator::class,
    ];
  }

  /**
   * The services that the bundle needs from the application are added as minimal definitions.
   *
   * @param bool $optional Also add Doctrine and the translator
   *
   * @return ContainerBuilder
   */
  private static function loadExtension(bool $optional = true): ContainerBuilder
  {
    $container = new ContainerBuilder();
    $container->setDefinition('event_dispatcher', new Definition(EventDispatcher::class));
    $container->setDefinition('request_stack', new Definition(RequestStack::class));
    $container->setDefinition('http_client', new Definition(MockHttpClient::class));
    if ($optional)
    {
      $container->setDefinition('doctrine', new Definition(ManagerRegistry::class)->setSynthetic(true)->setPublic(true));
    }
    new AustralFormExtension()->load([], $container);

    return $container;
  }

  /**
   * @return ContainerBuilder
   */
  private function compiledContainer(): ContainerBuilder
  {
    $container = self::loadExtension();
    self::makePublic($container);
    $container->compile();
    // The registry is only stored by the guesser: a stub is enough.
    $container->set('doctrine', $this->createStub(ManagerRegistry::class));

    return $container;
  }

  /**
   * @param ContainerBuilder $container
   *
   * @return void
   */
  private static function makePublic(ContainerBuilder $container): void
  {
    foreach ($container->getDefinitions() as $id => $definition)
    {
      if (str_starts_with($id, 'Austral\\FormBundle\\'))
      {
        $definition->setPublic(true);
      }
    }
  }
}
