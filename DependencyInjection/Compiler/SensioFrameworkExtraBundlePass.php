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
use Yarhon\RouteGuardBundle\Security\TestProvider\SensioExtraProvider;
use Yarhon\RouteGuardBundle\Security\TestResolver\SensioExtraResolver;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioFrameworkExtraBundlePass implements CompilerPassInterface
{
    /**
     * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/blob/v5.1.0/Resources/config/security.xml
     *
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sensio_framework_extra.security.listener') &&
            !$container->hasDefinition('framework_extra_bundle.event.is_granted')) {
            $container->removeDefinition(SensioExtraProvider::class);
            $container->removeDefinition(SensioExtraResolver::class);
        }
    }
}
