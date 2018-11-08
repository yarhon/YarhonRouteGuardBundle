<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yarhon\RouteGuardBundle\DependencyInjection\Compiler\SensioFrameworkExtraBundlePass;
use Yarhon\RouteGuardBundle\Security\TestProvider\SensioSecurityProvider;
use Yarhon\RouteGuardBundle\Security\TestResolver\SensioSecurityResolver;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioFrameworkExtraBundlePassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var SensioFrameworkExtraBundlePass
     */
    private $pass;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->register(SensioSecurityProvider::class);
        $this->container->register(SensioSecurityResolver::class);
        $this->pass = new SensioFrameworkExtraBundlePass();
    }

    public function testProcessWithoutExtraBundle()
    {
        $this->pass->process($this->container);

        $this->assertFalse($this->container->hasDefinition(SensioSecurityProvider::class));
        $this->assertFalse($this->container->hasDefinition(SensioSecurityResolver::class));
    }

    public function testProcessWithExtraBundleSecurityListener()
    {
        $this->container->register('sensio_framework_extra.security.listener');

        $this->pass->process($this->container);

        $this->assertTrue($this->container->hasDefinition(SensioSecurityProvider::class));
        $this->assertTrue($this->container->hasDefinition(SensioSecurityResolver::class));
    }

    public function testProcessWithExtraBundleIsGrantedListener()
    {
        $this->container->register('framework_extra_bundle.event.is_granted');

        $this->pass->process($this->container);

        $this->assertTrue($this->container->hasDefinition(SensioSecurityProvider::class));
        $this->assertTrue($this->container->hasDefinition(SensioSecurityResolver::class));
    }
}
