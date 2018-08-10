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
use Yarhon\RouteGuardBundle\CacheWarmer\AccessMapCacheWarmer;
use Yarhon\RouteGuardBundle\Routing\RouteCollection\RemoveIgnoredTransformer;
use Yarhon\RouteGuardBundle\Twig\Extension\RoutingExtension;
use Yarhon\RouteGuardBundle\Twig\RoutingRuntime;

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

        $definition = $container->getDefinition(RemoveIgnoredTransformer::class);
        $definition->replaceArgument(0, $config['ignore_controllers']);

        $definition = $container->getDefinition(RoutingExtension::class);
        $definition->replaceArgument(0, $config['twig']);

        $definition = $container->getDefinition(AccessMapCacheWarmer::class);
        $definition->replaceArgument(1, $config['cache_dir']);
    }
}
