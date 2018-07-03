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
use Yarhon\LinkGuardBundle\Security\AccessMap;
use Yarhon\LinkGuardBundle\Security\Provider\SymfonyAccessControlProvider;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonySecurityBundlePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasExtension('security')) {
            return;
        }

        $accessControl = [];

        $configs = $container->getExtensionConfig('security');
        foreach ($configs as $config) {
            if (!isset($config['access_control'])) {
                continue;
            }

            $accessControl = array_merge($accessControl, $config['access_control']);
        }

        $accessControlProvider = $container->getDefinition(SymfonyAccessControlProvider::class);
        foreach ($accessControl as $accessControlRule) {
            $accessControlProvider->addMethodCall('addRule', [$accessControlRule]);
        }

        $accessMapDefinition = $container->getDefinition(AccessMap::class);
        $accessMapDefinition->addMethodCall('addProvider', [new Reference(SymfonyAccessControlProvider::class)]);
    }
}
