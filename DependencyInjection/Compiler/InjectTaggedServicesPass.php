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
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;

/**
 * Injects tagged services collection as a method call argument.
 * Method call should initially contain one argument - empty collection (i.e. <argument type="collection" />).
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
        $this->inject($container, [AccessMapBuilder::class, 'setRouteCollectionTransformers'], 'yarhon_route_guard.route_collection_transformer');
        $this->inject($container, [AccessMapBuilder::class, 'setAuthorizationProviders'], 'yarhon_route_guard.authorization_provider');
    }

    private function inject(ContainerBuilder $container, $destination, $tagName)
    {
        $services = $this->findAndSortTaggedServices($tagName, $container);

        $definition = $container->getDefinition($destination[0]);
        $methodCalls = $definition->getMethodCalls();

        foreach ($methodCalls as &$methodCall) {
            if ($destination[1] === $methodCall[0] && [[]] === $methodCall[1]) {
                $methodCall[1] = [$services];
                break;
            }
        }

        $definition->setMethodCalls($methodCalls);
    }
}
