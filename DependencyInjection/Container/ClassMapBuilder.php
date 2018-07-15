<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection\Container;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ClassMapBuilder
{
    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    public function build(ContainerBuilder $container)
    {
        $map = [];

        foreach ($container->getDefinitions() as $id => $definition) {
            $map[$id] = $definition->getClass();
        }

        foreach ($container->getAliases() as $id => $alias) {
            $map[$id] = $map[(string) $alias];
        }

        return $map;
    }
}
