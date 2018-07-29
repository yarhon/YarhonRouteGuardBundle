<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\SymfonySecurityBundlePass;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\SensioFrameworkExtraBundlePass;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\RouterPass;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\ContainerClassMapPass;
use Yarhon\LinkGuardBundle\DependencyInjection\Container\ForeignExtensionAccessor;
use Yarhon\LinkGuardBundle\DependencyInjection\Container\ClassMapBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class YarhonLinkGuardBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $foreignExtensionAccessor = new ForeignExtensionAccessor();
        $classMapBuilder = new ClassMapBuilder();

        $container->addCompilerPass(new SymfonySecurityBundlePass($foreignExtensionAccessor), PassConfig::TYPE_BEFORE_REMOVING, 100);
        $container->addCompilerPass(new SensioFrameworkExtraBundlePass(), PassConfig::TYPE_BEFORE_REMOVING, 101);
        $container->addCompilerPass(new RouterPass(), PassConfig::TYPE_BEFORE_REMOVING, 102);

        // We need only public services for the class map, so we include this pass at the very end, after removing all private services.
        $container->addCompilerPass(new ContainerClassMapPass($classMapBuilder), PassConfig::TYPE_REMOVE, 0);
    }
}
