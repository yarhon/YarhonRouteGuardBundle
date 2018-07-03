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
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\UrlGeneratorPass;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class YarhonLinkGuardBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SymfonySecurityBundlePass(), PassConfig::TYPE_BEFORE_REMOVING, 100);
        $container->addCompilerPass(new SensioFrameworkExtraBundlePass(), PassConfig::TYPE_BEFORE_REMOVING, 101);
        $container->addCompilerPass(new UrlGeneratorPass(), PassConfig::TYPE_BEFORE_REMOVING, 102);
    }
}
