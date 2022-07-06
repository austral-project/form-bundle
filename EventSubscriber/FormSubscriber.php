<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Austral\FormBundle\EventSubscriber;

use Austral\FormBundle\Event\FormEvent;
use Austral\FormBundle\Event\FormFieldEvent;
use Austral\FormBundle\Field\Base\FieldInterface;
use Austral\FormBundle\Field\PasswordField;
use Austral\FormBundle\Field\TextField;

use Austral\FormBundle\Field\UploadField;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\ToolsBundle\AustralTools;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Constraints as Constraints;
use function Symfony\Component\String\u;

/**
 * Austral FormSubscriber.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class FormSubscriber implements EventSubscriberInterface
{
  /**
   * @return array
   */
  public static function getSubscribedEvents(): array
  {
    return [
      FormEvent::EVENT_AUSTRAL_FORM_VALIDATE              =>  ["formValidate", 1024],
      FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER =>  ["formAddAutoFields", 0],
      FormEvent::EVENT_AUSTRAL_FORM_INIT_END              =>  ["formInitEnd", 0],
      FormFieldEvent::EVENT_AUSTRAL_FORM_PRE_SET_DATA     =>  ["fieldPreSetData", 0],
      FormFieldEvent::EVENT_AUSTRAL_FIELD_CONFIGURATION   =>  ["fieldConfiguration", 0],
    ];
  }

  /**
   * @param FormEvent $formEvent
   */
  public function formValidate(FormEvent $formEvent)
  {
    if($formEvent->getFormMapper()->getSubFormMappers())
    {
      /** @var FormMapper $subMapper */
      foreach ($formEvent->getFormMapper()->getSubFormMappers() as $subMapper)
      {
        if(isset($formEvent->getForm()[$subMapper->getName()]))
        {
          $subFormEvent = new FormEvent($subMapper);
          $subFormEvent->setForm($formEvent->getForm()[$subMapper->getName()]);
          $subMapper->getDispatcher()->dispatch($subFormEvent, FormEvent::EVENT_AUSTRAL_FORM_VALIDATE);
        }
      }
    }
  }

  /**
   * @param FormEvent $formEvent
   */
  public function formAddAutoFields(FormEvent $formEvent)
  {
    if($formEvent->getFormMapper()->getSubFormMappers())
    {
      /** @var FormMapper $subMapper */
      foreach ($formEvent->getFormMapper()->getSubFormMappers() as $subMapper)
      {
        $subFormEvent = new FormEvent($subMapper);
        $subMapper->getDispatcher()->dispatch($subFormEvent, FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER);
      }
    }
  }

  /**
   * @param FormEvent $formEvent
   */
  public function formInitEnd(FormEvent $formEvent)
  {
    if(strpos($formEvent->getFormMapper()->getName(), "austral") !== false)
    {
      /** @var FieldInterface $field */
      foreach($formEvent->getFormMapper()->allFields() as $field)
      {
        $this->hydrateTemplateDefault($field, $formEvent->getFormMapper()->getPathToTemplateDefault());
      }
      $this->hydrateTemplateDefaultBySubFormMapper($formEvent->getFormMapper()->getAllSubFormMapper());
    }
  }

  /**
   * @param array $subFormMappers
   *
   * @return void
   */
  protected function hydrateTemplateDefaultBySubFormMapper(array $subFormMappers = array())
  {
    /** @var FormMapper $subMapper */
    foreach($subFormMappers as $subMapper)
    {
      /** @var FieldInterface $field */
      foreach($subMapper->allFields() as $field)
      {
        $this->hydrateTemplateDefault($field, $subMapper->getPathToTemplateDefault());
      }
      $this->hydrateTemplateDefaultBySubFormMapper($subMapper->getAllSubFormMapper());
    }
  }

  /**
   * @param FieldInterface $field
   * @param $pathToTemplate
   *
   * @return void
   */
  protected function hydrateTemplateDefault(FieldInterface $field, $pathToTemplate)
  {
    if(($templatePath = $field->getTemplatePath()) && $field->isDefaultTemplate())
    {
      $field->setTemplatePath(u('/')->join(array(
            u($pathToTemplate)->trimEnd("/")->toString(),
            u($templatePath)->trimStart("/")->toString()
          )
        )->trimStart("/")->toString()
      );
      $field->setDefaultTemplate(false);
    }
  }

  /**
   * @var array
   */
  protected array $contraintsByTypes = array(
    "string"  =>  "string",
    "integer" =>  "integer",
    "float"   =>  "numeric"
  );

  /**
   * @param FormFieldEvent $formFieldEvent
   */
  public function fieldPreSetData(FormFieldEvent $formFieldEvent)
  {

  }

  /**
   * @param FormFieldEvent $formFieldEvent
   */
  public function fieldConfiguration(FormFieldEvent $formFieldEvent)
  {
    $field = $formFieldEvent->getField();
    $fieldsMapping = $formFieldEvent->getFormMapper()->getFieldsMapping();

    if($field->getAutoConstraints()) {

      if($mapping = AustralTools::getValueByKey($fieldsMapping, $field->getFieldname())) {
        if(AustralTools::getValueByKey($mapping, "nullable", true) === false) {
          if($field->getRequired() !== "no") {
            $field->setRequired(true);
          }
        }

        $fieldTypeMapping = AustralTools::getValueByKey($mapping, "type", null) ;
        if($fieldTypeMapping === "string") {
          $field->addConstraint(new Constraints\Length(array(
                "max" => AustralTools::getValueByKey($mapping, "length"),
                "maxMessage" => "errors.length.max"
              )
            )
          );
        }

        if($field instanceof TextField) {
          $messageErrorByTypeKey = "errors.type.{$fieldTypeMapping}";
          if(array_key_exists($fieldTypeMapping, $this->contraintsByTypes))
          {
            $field->addConstraint(new Constraints\Type(array(
                  "type"=> $this->contraintsByTypes[$fieldTypeMapping],
                  "message" => $messageErrorByTypeKey
                )
              )
            );
          }
          elseif($fieldTypeMapping === "datetime") {
            $field->addConstraint(new Constraints\DateTime(array(
                  "message" => $messageErrorByTypeKey
                )
              )
            );
          }
          if(AustralTools::getValueByKey($mapping, "unique", null) === true) {
            $formFieldEvent->getFormMapper()->addUniqueField($field->getFieldname());
          }
        }
        elseif($formFieldEvent->getField() instanceof UploadField) {
          $field = $formFieldEvent->getField();
          $field->addConstraint(new Constraints\File(array(
                'maxSize' => $field->getMaxSize(),
              )
            )
          );
        }
      }

      if($field->getRequired()) {
        $field->addConstraint(new Constraints\NotNull());
      }

      if($field instanceof PasswordField) {
        $field->setRequired(true);
        if($pregMatch = $field->pregMatch())
        {
          $field->addConstraint(new Constraints\Regex(array(
                "pattern" => $pregMatch,
                "message" => "errors.{$field->getFieldname()}.regex"
              )
            )
          );
        }
      }
    }
    foreach ($field->getConstraints() as $contraint)
    {
      if($contraint instanceof Constraints\NotNull) {
        $field->setRequired(true);
      }
    }
  }

}