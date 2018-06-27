<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use NeonLight\SecureLinksBundle\DependencyInjection\Compiler\SymfonySecurityBundlePass;
use NeonLight\SecureLinksBundle\DependencyInjection\Compiler\SensioFrameworkExtraBundlePass;
use NeonLight\SecureLinksBundle\DependencyInjection\Compiler\UrlGeneratorPass;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class NeonLightSecureLinksBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SymfonySecurityBundlePass(), PassConfig::TYPE_BEFORE_REMOVING, 100);
        $container->addCompilerPass(new SensioFrameworkExtraBundlePass(), PassConfig::TYPE_BEFORE_REMOVING, 101);
        $container->addCompilerPass(new UrlGeneratorPass(), PassConfig::TYPE_BEFORE_REMOVING, 102);
    }
}
