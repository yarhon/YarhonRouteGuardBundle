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
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Yarhon\LinkGuardBundle\DependencyInjection\Configurator\AccessMapBuilderConfigurator;
use Yarhon\LinkGuardBundle\DependencyInjection\Configurator\UrlGeneratorConfigurator;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $parameterName = 'link_guard.router_service_id';

        if (!$container->hasParameter($parameterName)) {
            throw new ParameterNotFoundException($parameterName);
        }

        $routerServiceId = $container->getParameter($parameterName);

        if (!$container->has($routerServiceId)) {
            throw new ServiceNotFoundException((string) $routerServiceId, AccessMapBuilderConfigurator::class);
        }

        $accessMapBuilderConfiguratorDefinition = $container->getDefinition(AccessMapBuilderConfigurator::class);
        $accessMapBuilderConfiguratorDefinition->replaceArgument(0, new Reference($routerServiceId));

        $routerDefinition = $container->getDefinition($routerServiceId);

        $configurator = [new Reference(UrlGeneratorConfigurator::class), 'configure'];

        // !!! TODO: think about this
        if (null !== $routerDefinition->getConfigurator()) {
            $routerDefinition->setConfigurator($configurator);
        }
    }
}
