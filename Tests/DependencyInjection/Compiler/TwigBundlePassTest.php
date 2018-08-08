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
use Symfony\Component\DependencyInjection\Definition;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\TwigBundlePass;
use Yarhon\LinkGuardBundle\Twig\Extension\RoutingExtension;
use Yarhon\LinkGuardBundle\Twig\RoutingRuntime;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TwigBundlePassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var TwigBundlePass
     */
    private $pass;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->register(RoutingExtension::class)
            ->setArgument(0, []);
        $this->container->register(RoutingRuntime::class);

        $this->pass = new TwigBundlePass();
    }

    public function testProcessWithoutRoutingExtension()
    {
        $this->pass->process($this->container);

        //$this->assertFalse($this->container->hasDefinition(SensioSecurityProvider::class));
    }

    public function testProcessWithRoutingExtension()
    {
        //$this->container->register('sensio_framework_extra.controller.listener');

        $this->pass->process($this->container);

        //$this->assertTrue($this->container->hasDefinition(SensioSecurityProvider::class));
    }
}
