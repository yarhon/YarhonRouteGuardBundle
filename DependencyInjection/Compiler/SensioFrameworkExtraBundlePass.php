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
use Yarhon\RouteGuardBundle\Security\TestProvider\SensioSecurityProvider;
use Yarhon\RouteGuardBundle\Security\TestResolver\SensioSecurityResolver;
use Yarhon\RouteGuardBundle\Security\Authorization\SensioSecurityExpressionVoter;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioFrameworkExtraBundlePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sensio_framework_extra.controller.listener')) {
            var_dump('remove sensio', $container->getServiceIds());

            $container->removeDefinition(SensioSecurityProvider::class);
            $container->removeDefinition(SensioSecurityResolver::class);
            $container->removeDefinition(SensioSecurityExpressionVoter::class);

            return;
        }

        if (!$container->hasDefinition('sensio_framework_extra.security.expression_language.default')) {
            $container->removeDefinition(SensioSecurityExpressionVoter::class);
        }
    }
}
