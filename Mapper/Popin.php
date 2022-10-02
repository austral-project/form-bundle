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
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Austral Popin Mapper.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class Popin extends MapperElement implements MapperElementInterface
{

  /**
   * @var string|null
   */
  protected ?string $fieldname = null;

  /**
   * @var string|null
   */
  protected ?string $popinKeyId = null;

  /**
   * @var array
   */
  protected array $options = array();

  /**
   * Choices constructor.
   *
   * @param string $name
   * @param MapperElementInterface $parent
   * @param string|null $fieldname
   * @param array $options
   *
   * @throws \Exception
   */
  public function __construct(string $name, MapperElementInterface $parent, string $fieldname = null, array $options = array())
  {
    $this->name = $name;
    $this->isView = true;
    $this->fieldname = $fieldname;
    $slugger = new AsciiSlugger();
    $this->keyname = "popin-".strtolower($slugger->slug($name))."_".Uuid::uuid4()->toString();
    $this->popinKeyId = $this->keyname;
    $this->parent = $parent;

    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefault('attr', array());
    $resolver->setDefault('popin', function (OptionsResolver $resolverChild) {
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
          "data"                =>  array()
        )
      );
      $resolverChild->setAllowedTypes('entitled', array('string', "null"));
      $resolverChild->setAllowedTypes('picto', array('string', "null"));
      $resolverChild->setAllowedTypes('class', array('string', "null"));
      $resolverChild->setAllowedTypes('data', array('array'));
    });
  }

  /**
   * @param string $popinKeyId
   *
   * @return $this
   */
  public function setPopinKeyId(string $popinKeyId): Popin
  {
    $this->popinKeyId = $popinKeyId;
    return $this;
  }

  /**
   * @return string
   */
  public function getPopinKeyId(): string
  {
    return $this->popinKeyId;
  }

  /**
   * @return mixed
   */
  public function getPopin()
  {
    return $this->options['popin'];
  }

  public function getButton()
  {
    return $this->options['button'];
  }

  /**
   * @return array
   */
  public function getAttr(): array
  {
    return $this->options['attr'];
  }

  /**
   * @return string
   */
  public function getAttrString(): string
  {
    $attrString = "";
    foreach($this->getAttr() as $key => $value)
    {
      $attrString .= $key."=".(is_array($value) ? json_encode($value) : $value);
    }
    return $attrString;
  }

  /**
   * @return string|null
   */
  public function getFieldname(): string
  {
    return $this->fieldname;
  }

  /**
   * @return MapperElementInterface|FormMapper|FormField|GroupFields
   */
  public function end(): MapperElementInterface
  {
    return $this->parent;
  }

}