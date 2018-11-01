<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ClassMap;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ClassMapBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ContainerClassMapPass implements CompilerPassInterface
{
    /**
     * @var ContainerBuilder
     */
    private $classMapBuilder;

    /**
     * @param ClassMapBuilder $classMapBuilder
     */
    public function __construct(ClassMapBuilder $classMapBuilder)
    {
        $this->classMapBuilder = $classMapBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $map = $this->classMapBuilder->build($container);

        $classMapDefinition = $container->getDefinition(ClassMap::class);
        $classMapDefinition->replaceArgument(0, $map);
    }
}
