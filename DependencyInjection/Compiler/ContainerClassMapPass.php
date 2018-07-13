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
use Yarhon\LinkGuardBundle\DependencyInjection\ContainerClassMapBuilder;
use Yarhon\LinkGuardBundle\Controller\ContainerControllerNameResolver;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ContainerClassMapPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $classMapBuilder = new ContainerClassMapBuilder();

        $classMap = $classMapBuilder->build($container);

        $controllerNameResolverDefinition = $container->getDefinition(ContainerControllerNameResolver::class);
        $controllerNameResolverDefinition->replaceArgument(0, $classMap);
    }
}
