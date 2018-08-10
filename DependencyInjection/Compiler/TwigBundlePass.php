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
use Yarhon\RouteGuardBundle\Twig\Extension\RoutingExtension;
use Yarhon\RouteGuardBundle\Twig\RoutingRuntime;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TwigBundlePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('twig')) {
            $container->removeDefinition(RoutingExtension::class);
            $container->removeDefinition(RoutingRuntime::class);

            return;
        }

        if (!$container->hasDefinition('twig.extension.routing')) {
            $definition = $container->getDefinition(RoutingExtension::class);
            $options = $definition->getArgument(0);
            $options['discover_routing_functions'] = false;
            $definition->replaceArgument(0, $options);
        }
    }
}
