<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\SensioFrameworkExtraBundlePass;
use Yarhon\LinkGuardBundle\Security\Provider\SensioSecurityProvider;

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
        $this->pass = new SensioFrameworkExtraBundlePass();
    }

    public function testProcessWithoutExtraBundle()
    {
        $this->pass->process($this->container);

        $this->assertFalse($this->container->hasDefinition(SensioSecurityProvider::class));
    }

    public function testProcessWithExtraBundle()
    {
        $this->container->register('sensio_framework_extra.controller.listener');

        $this->pass->process($this->container);

        $this->assertTrue($this->container->hasDefinition(SensioSecurityProvider::class));
    }
}
