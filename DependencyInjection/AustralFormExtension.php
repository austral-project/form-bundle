<?php
/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FormBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Austral Form Extension.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class AustralFormExtension extends Extension
{
  /**
   * {@inheritdoc}
   */
  public function load(array $configs, ContainerBuilder $container)
  {
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    $loader->load('services.yaml');
  }

  /**
   * @return string
   */
  public function getNamespace()
  {
    return 'https://austral.dev/schema/dic/austral_form_mapper';
  }

}
