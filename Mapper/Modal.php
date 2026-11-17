<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Mapper;

use Austral\FormBundle\Mapper\Base\MapperElementInterface;
use Austral\FormBundle\Mapper\Base\MapperElement;

use Ramsey\Uuid\Uuid;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Modal Mapper.
 * @template T of object
 *
 * @extends MapperElement<T>
 * @implements MapperElementInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class Modal extends MapperElement implements MapperElementInterface
{

  /**
   * @var string
   */
  public static string $keynameBefore = "modal-";

  /**
   * @var string|null
   */
  protected ?string $fieldname = null;

  /**
   * @var string
   */
  protected string $modalKeyId;

  /**
   * @var array<string, mixed>
   */
  protected array $options = [];

  /**
   * Choices constructor.
   *
   * @param string $name
   * @param MapperElementInterface<T> $parent
   * @param string|null $fieldname
   * @param array<string, mixed> $options
   *
   * @throws \Exception
   */
  public function __construct(string $name, MapperElementInterface $parent, ?string $fieldname = null, array $options = [])
  {
    self::$keynameAfter = "_".Uuid::uuid4()->toString();
    parent::__construct($name, $parent);
    $this->isView = true;
    $this->fieldname = $fieldname;
    $this->modalKeyId = $this->keyname;
    $this->parent = $parent;

    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefault('attr', []);
    $resolver->setDefault('modal', function (OptionsResolver $resolverChild) {
      $resolverChild->setDefaults(array(
          "id"            =>  "master",
          "class"         =>  null,
          "template"      =>  null,
        )
      );
      $resolverChild->setAllowedTypes('id', array('string'));
      $resolverChild->setAllowedTypes('class', array('string', "null"));
      $resolverChild->setAllowedTypes('template', array('string', "null"));
    });
    $resolver->setDefault('button', function (OptionsResolver $resolverChild) {
      $resolverChild->setDefaults(array(
          "entitled"            =>  "",
          "picto"               =>  "austral-picto-edit",
          "class"               =>  "button-picto",
          "data"                =>  []
        )
      );
      $resolverChild->setAllowedTypes('entitled', array('string', "null"));
      $resolverChild->setAllowedTypes('picto', array('string', "null"));
      $resolverChild->setAllowedTypes('class', array('string', "null"));
      $resolverChild->setAllowedTypes('data', array('array'));
    });
  }

  /**
   * @param string $modalKeyId
   *
   * @return $this
   */
  public function setModalKeyId(string $modalKeyId): Modal
  {
    $this->modalKeyId = $modalKeyId;
    return $this;
  }

  /**
   * @return string
   */
  public function getModalKeyId(): string
  {
    return $this->modalKeyId;
  }

  /**
   * @return string
   */
  public function getModal(): string
  {
    return $this->options['modal'];
  }

  /**
   * getButton
   *
   * @return array<string, mixed>
   */
  public function getButton(): array
  {
    return $this->options['button'];
  }

  /**
   * @return string|null
   */
  public function getFieldname(): ?string
  {
    return $this->fieldname;
  }

}