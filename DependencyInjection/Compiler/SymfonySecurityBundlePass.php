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
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;
use Yarhon\LinkGuardBundle\Security\Provider\SymfonyAccessControlProvider;
use Yarhon\LinkGuardBundle\DependencyInjection\Container\ForeignExtensionAccessor;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonySecurityBundlePass implements CompilerPassInterface
{
    /**
     * @var ForeignExtensionAccessor
     */
    private $extensionAccessor;

    public function __construct(ForeignExtensionAccessor $extensionAccessor)
    {
        $this->extensionAccessor = $extensionAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasExtension('security')) {
            $container->removeDefinition(SymfonyAccessControlProvider::class);

            return;
        }

        $config = $this->extensionAccessor->getProcessedConfig($container, 'security');

        if (!isset($config['access_control'])) {
            $container->removeDefinition(SymfonyAccessControlProvider::class);

            return;
        }

        $accessControl = $config['access_control'];

        $accessControlProvider = $container->getDefinition(SymfonyAccessControlProvider::class);
        foreach ($accessControl as $accessControlRule) {
            $accessControlProvider->addMethodCall('addRule', [$accessControlRule]);
        }

        $accessMapBuilderDefinition = $container->getDefinition(AccessMapBuilder::class);
        $accessMapBuilderDefinition->addMethodCall('addAuthorizationProvider', [new Reference(SymfonyAccessControlProvider::class)]);
    }
}
