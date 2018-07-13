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

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ContainerClassMapBuilder
{
    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    public function build(ContainerBuilder $container)
    {
        $classMap = [];

        foreach ($container->getDefinitions() as $id => $definition) {
            $classMap[$id] = $definition->getClass();
        }

        foreach ($container->getAliases() as $id => $alias) {
            $classMap[$id] = $classMap[(string) $alias];
        }

        return $classMap;
    }
}
