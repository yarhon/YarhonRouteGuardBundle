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
                ->booleanNode('override_url_generator')->defaultValue(true)->end()
            ->end();

        return $treeBuilder;
    }
}
