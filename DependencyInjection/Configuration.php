<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * RouteGuardBundle configuration structure.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    private static $symfonyControllers = [
        'twig.controller.preview_error',     // 'Symfony\Bundle\TwigBundle\Controller\PreviewErrorController'
        'web_profiler.controller.profiler',  // 'Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController'
        'web_profiler.controller.router',    // 'Symfony\Bundle\WebProfilerBundle\Controller\RouterController',
        'web_profiler.controller.exception', // 'Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController'
    ];

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->validate()
                ->always(function ($rootNode) {
                    $dataCollectorNode = &$rootNode['data_collector'];

                    $dataCollectorNode['ignore_controllers'] = array_merge($dataCollectorNode['ignore_controllers'], $dataCollectorNode['ignore_controllers_symfony']);
                    unset($dataCollectorNode['ignore_controllers_symfony']);

                    return $rootNode;
                })
            ->end()
            ->children()
                ->arrayNode('data_collector')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('ignore_controllers')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('ignore_controllers_symfony')
                            ->prototype('scalar')->end()
                            ->defaultValue(static::$symfonyControllers)
                        ->end()
                        ->booleanNode('ignore_exceptions')->defaultValue(false)->end()
                    ->end()
                ->end()
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('tag_name')->defaultValue('route')->end()
                        ->scalarNode('tag_variable_name')->defaultValue('_route')->end()
                        ->booleanNode('discover_routing_functions')->defaultValue(true)->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
