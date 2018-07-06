<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Yarhon\LinkGuardBundle\CacheWarmer\RouteCacheWarmer;
use Yarhon\LinkGuardBundle\DependencyInjection\Configurator\UrlGeneratorConfigurator;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class YarhonLinkGuardExtension extends Extension
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

        $definition = $container->getDefinition(RouteCacheWarmer::class);
        $definition->replaceArgument(1, $config['cache_dir']);

        $definition = $container->getDefinition(UrlGeneratorConfigurator::class);
        $definition->replaceArgument(1, $config['override_url_generator']);

    }
}
