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
use Symfony\Component\DependencyInjection\Reference;
use Yarhon\RouteGuardBundle\Security\TestProvider\SymfonyAccessControlProvider;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ForeignExtensionAccessor;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionFactory;

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
     * @var int
     */
    private $versionId;

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

        $this->processAccessControl($container);
        $this->processExpressionLanguage($container);
    }

    private function processAccessControl(ContainerBuilder $container)
    {
        $config = $this->extensionAccessor->getProcessedConfig($container, 'security');

        if (!isset($config['access_control']) || 0 === count($config['access_control'])) {
            $container->removeDefinition(SymfonyAccessControlProvider::class);

            return;
        }

        $accessControlProvider = $container->getDefinition(SymfonyAccessControlProvider::class);
        $accessControlProvider->addMethodCall('importRules', [$config['access_control']]);
    }

    private function processExpressionLanguage(ContainerBuilder $container)
    {
        $serviceId = 'security.expression_language';

        if (!$container->hasDefinition($serviceId)) {
            return;
        }

        $expressionLanguage = $container->getDefinition($serviceId);
        $useExpressionLanguageCache = isset($expressionLanguage->getArguments()[0]);

        // See \Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter::getVariables
        $defaultNames = ['token', 'user', 'object', 'subject', 'roles', 'trust_resolver'];
        if ($this->versionId >= 40200) {
            $defaultNames[] = 'auth_checker';
        }

        $expressionFactory = $container->getDefinition(ExpressionFactory::class);
        $expressionFactory->replaceArgument(0, new Reference($serviceId));
        $expressionFactory->replaceArgument(1, $defaultNames);
        $expressionFactory->replaceArgument(2, $useExpressionLanguageCache);
    }
}
