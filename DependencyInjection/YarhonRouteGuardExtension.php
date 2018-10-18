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
use Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderInterface;
use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentValueResolverInterface;

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
        $loader->load('cache.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setConfigParameters($container, $config);
        $this->registerAutoConfiguration($container);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function setConfigParameters(ContainerBuilder $container, array $config)
    {
        $builderOptions = [
            'ignore_controllers' => $config['ignore_controllers'],
        ];

        $definition = $container->getDefinition(AccessMapBuilder::class);
        $definition->replaceArgument(1, $builderOptions);

        $definition = $container->getDefinition(RoutingExtension::class);
        $definition->replaceArgument(0, $config['twig']);
    }

    private function registerAutoConfiguration(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(TestProviderInterface::class)->addTag('yarhon_route_guard.test_provider');
        $container->registerForAutoconfiguration(TestResolverInterface::class)->addTag('yarhon_route_guard.test_resolver');
        $container->registerForAutoconfiguration(ArgumentValueResolverInterface::class)->addTag('yarhon_route_guard.argument_value_resolver');
    }
}
