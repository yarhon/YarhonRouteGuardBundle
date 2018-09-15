<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Yarhon\RouteGuardBundle\Security\AccessMapBuilder;
use Yarhon\RouteGuardBundle\Twig\Extension\RoutingExtension;


/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class YarhonRouteGuardExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setConfigParameters($config, $container);
    }

    private function setConfigParameters(array $config, ContainerBuilder $container)
    {
        //$definition = $container->getDefinition(AccessMapBuilder::class);
        //$definition->addMethodCall('setIgnoredControllers', [$config['ignore_controllers']]);

        $definition = $container->getDefinition(RoutingExtension::class);
        $definition->replaceArgument(0, $config['twig']);
    }
}
