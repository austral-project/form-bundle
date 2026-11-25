<?php

/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\Field;

use Austral\FormBundle\Validator\Recaptcha;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Recaptcha Field.
 *
 * Hidden, unmapped field filled by the reCAPTCHA script (data-recaptcha: public key, data-type-action: action).
 * It is always checked by the Recaptcha constraint (built from its options, with the secret key): the option
 * "auto_constraints" does not apply to it.
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
final class RecaptchaField extends Field
{
  public const string FIELD_NAME = '_g-recaptcha-response';

  /**
   * @var class-string|null
   */
  protected ?string $symfonyType = HiddenType::class;

  /**
   * @param array<string, mixed> $options
   */
  public function __construct(array $options = [])
  {
    parent::__construct(self::FIELD_NAME, $options);
  }

  /**
   * @param array<string, mixed> $options
   *
   * @return self
   */
  public static function create(array $options = []): self
  {
    return new self($options);
  }

  /**
   * {@inheritdoc}
   */
  protected function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults([
      'label' => false,
      'mapped' => false,
      'action' => 'registry',
    ]);

    $resolver->setRequired(['secret_key', 'public_key']);
    $resolver->setAllowedTypes('secret_key', 'string')
      ->setAllowedTypes('public_key', 'string')
      ->setAllowedTypes('action', 'string');

    $resolver->setOptions('score', static function (OptionsResolver $score): void {
      $score->setDefaults(['limit' => 0.7, 'message' => 'bot-detected'])
        ->setAllowedTypes('limit', ['float', 'int'])
        ->setAllowedTypes('message', 'string');
    });
  }

  /**
   * @return string
   */
  public function getPublicKey(): string
  {
    return $this->options['public_key'];
  }

  /**
   * @return string
   */
  public function getAction(): string
  {
    return $this->options['action'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSymfonyOptions(): array
  {
    $options = parent::getSymfonyOptions();
    $options += ['error_bubbling' => false];
    $options['attr']['data-recaptcha'] = $this->options['public_key'];
    $options['attr']['data-type-action'] = $this->options['action'];
    $options['constraints'] = array_merge($options['constraints'] ?? [], [$this->getRecaptchaConstraint()]);

    return $options;
  }

  /**
   * Built from the current options, added to the Symfony options whatever "auto_constraints" is.
   *
   * @return Recaptcha
   */
  public function getRecaptchaConstraint(): Recaptcha
  {
    return new Recaptcha(
      secretKey: $this->options['secret_key'],
      action: $this->options['action'],
      scoreLimit: (float) $this->options['score']['limit'],
      scoreMessage: $this->options['score']['message'],
      message: 'errors.captcha',
    );
  }
}
