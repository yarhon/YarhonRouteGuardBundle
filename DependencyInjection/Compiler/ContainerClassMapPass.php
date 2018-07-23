<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Yarhon\LinkGuardBundle\DependencyInjection\Container\ClassMap;
use Yarhon\LinkGuardBundle\DependencyInjection\Container\ClassMapBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ContainerClassMapPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $builder = new ClassMapBuilder();
        $map = $builder->build($container);

        $controllerNameResolverDefinition = $container->getDefinition(ClassMap::class);
        $controllerNameResolverDefinition->replaceArgument(0, $map);
    }
}
