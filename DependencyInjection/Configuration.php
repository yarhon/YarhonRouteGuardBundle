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
            ->validate()
                ->always(function ($node) {
                    $node['ignore_controllers'] = array_merge($node['ignore_controllers'], $node['ignore_controllers_symfony']);
                    unset($node['ignore_controllers_symfony']);

                    return $node;
                })
            ->end()
            ->children()
                ->arrayNode('ignore_controllers')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('ignore_controllers_symfony')
                    ->prototype('scalar')->end()
                    ->defaultValue(static::$symfonyControllers)
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
