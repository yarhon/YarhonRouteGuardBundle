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
 * We use CompilerPass to inject tagged services for compatibility with Symfony 3.3.
 * Starting from Symfony 3.4. we can use <argument type="tagged" tag="link_guard.route_collection_transformer" />
 * in services.xml and remove this CompilerPass.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteCollectionTransformerTaggedPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tagName = 'link_guard.route_collection_transformer';

        $services = $this->findAndSortTaggedServices($tagName, $container);

        $definition = $container->getDefinition(AccessMapBuilder::class);
        $definition->addMethodCall('setRouteCollectionTransformers', [$services]);
    }
}
