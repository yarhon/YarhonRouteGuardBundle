<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * LinkGuardBundle configuration structure.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    private $symfonyControllers = [
        'Symfony\Bundle\TwigBundle\Controller\PreviewErrorController',
        'Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController',
        'Symfony\Bundle\WebProfilerBundle\Controller\RouterController',
        'Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController',
    ];

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->scalarNode('cache_dir')->defaultValue('link-guard')->end()
                ->arrayNode('ignore_controllers')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('ignore_controllers_symfony')
                    ->prototype('scalar')->end()
                    ->defaultValue($this->symfonyControllers)
                ->end()
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('tag_name')->defaultValue('routeifgranted')->end()
                        ->scalarNode('reference_var_name')->defaultValue('route_reference')->end()
                        ->booleanNode('discover')->defaultValue(true)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
