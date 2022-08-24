<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Validator;

use Austral\FormBundle\Field\RecaptchaField;
use Austral\FormBundle\Mapper\FormMapper;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Austral Validator Recaptcha.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class RecaptchaValidator extends ConstraintValidator
{

  /**
   * @var Request|null
   */
  private ?Request $request;

  /**
   * @var FormMapper
   */
  private FormMapper $formMapper;

  /**
   * @param RequestStack $requestStack
   * @param FormMapper $formMapper
   */
  public function __construct(RequestStack $requestStack, FormMapper $formMapper)
  {
    $this->request = $requestStack->getMainRequest();
    $this->formMapper = $formMapper;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint)
  {
    /** @var RecaptchaField $fieldRecaptcha */
    if($fieldRecaptcha = $this->formMapper->getField(RecaptchaField::FIELD_NAME))
    {
      if($recaptchaSecretKey = $fieldRecaptcha->getSecretKey())
      {
        if (!$constraint instanceof Recaptcha) {
          throw new UnexpectedTypeException($constraint, Recaptcha::class);
        }
        $httpClient = new NativeHttpClient(array(
          "verify_host" => false,
          "verify_peer" => false,
          "max_redirects" =>  5,
          "max_duration"  =>  2,
        ));
        $requestParameters = array(
          "body"    =>  array(
            "secret"    => $recaptchaSecretKey,
            "response"  => $value,
            "remoteip"  => $this->request->getClientIp()
          )
        );

        $response = $httpClient->request("POST", "https://www.google.com/recaptcha/api/siteverify", $requestParameters);
        $responseValue = json_decode($response->getContent(false), true);
        if($responseValue["success"] !== true)
        {
          $this->context->buildViolation($constraint->message)
            ->setParameter('{{ reason }}', implode(",", array_key_exists("error-codes", $responseValue) ? $responseValue["error-codes"] : array()))
            ->addViolation();
        }
      }
    }
  }
}
