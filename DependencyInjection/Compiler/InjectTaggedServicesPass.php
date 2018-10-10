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
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Yarhon\RouteGuardBundle\Security\AccessMapBuilder;
use Yarhon\RouteGuardBundle\Security\TestResolver\DelegatingTestResolver;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;

/**
 * Injects tagged services collection as a service argument.
 * Service argument must be initialized by an empty collection.
 *
 * We use CompilerPass to inject tagged services for compatibility with Symfony 3.3.
 * Starting from Symfony 3.4 we can use <argument type="tagged" tag="..." /> and remove this CompilerPass.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class InjectTaggedServicesPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->injectAsArgument($container, [AccessMapBuilder::class, 0], 'yarhon_route_guard.test_provider');
        $this->injectAsArgument($container, [DelegatingTestResolver::class, 0], 'yarhon_route_guard.test_resolver');
        $this->injectAsArgument($container, [ControllerArgumentResolver::class, 1], 'yarhon_route_guard.argument_value_resolver');
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $destination
     * @param string           $tagName
     */
    private function injectAsArgument(ContainerBuilder $container, $destination, $tagName)
    {
        $services = $this->findAndSortTaggedServices($tagName, $container);

        $definition = $container->getDefinition($destination[0]);

        $argument = $definition->getArgument($destination[1]);
        if ($argument === []) {
            $definition->replaceArgument($destination[1], $services);
        }
    }
}
