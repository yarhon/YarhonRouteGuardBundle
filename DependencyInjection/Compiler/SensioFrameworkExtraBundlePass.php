<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use NeonLight\SecureLinksBundle\Security\AccessMap;
use NeonLight\SecureLinksBundle\Security\Provider\SensioSecurityProvider;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioFrameworkExtraBundlePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sensio_framework_extra.controller.listener')) {
            return;
        }

        $accessMapDefinition = $container->getDefinition(AccessMap::class);
        $accessMapDefinition->addMethodCall('addProvider', [new Reference(SensioSecurityProvider::class)]);
    }
}
