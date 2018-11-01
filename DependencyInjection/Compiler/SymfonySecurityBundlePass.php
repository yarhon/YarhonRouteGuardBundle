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
use Yarhon\RouteGuardBundle\Security\TestProvider\SymfonyAccessControlProvider;
use Yarhon\RouteGuardBundle\Security\TestResolver\SymfonyAccessControlResolver;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ForeignExtensionAccessor;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonySecurityBundlePass implements CompilerPassInterface
{
    /**
     * @var ForeignExtensionAccessor
     */
    private $extensionAccessor;

    /**
     * @param ForeignExtensionAccessor $extensionAccessor
     */
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
            $container->removeDefinition(SymfonyAccessControlResolver::class);

            return;
        }

        $this->processAccessControl($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function processAccessControl(ContainerBuilder $container)
    {
        $extension = $container->getExtension('security');
        $config = $this->extensionAccessor->getProcessedConfig($container, $extension);

        if (!isset($config['access_control']) || 0 === count($config['access_control'])) {
            $container->removeDefinition(SymfonyAccessControlProvider::class);
            $container->removeDefinition(SymfonyAccessControlResolver::class);

            return;
        }

        $accessControlProvider = $container->getDefinition(SymfonyAccessControlProvider::class);
        $accessControlProvider->addMethodCall('importRules', [$config['access_control']]);
    }
}
