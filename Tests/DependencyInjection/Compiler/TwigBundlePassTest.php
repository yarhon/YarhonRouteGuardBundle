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
use Yarhon\RouteGuardBundle\DependencyInjection\Compiler\TwigBundlePass;
use Yarhon\RouteGuardBundle\Twig\Extension\RoutingExtension;
use Yarhon\RouteGuardBundle\Twig\RoutingRuntime;

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

    public function testWithoutTwig()
    {
        $this->pass->process($this->container);

        $this->assertFalse($this->container->hasDefinition(RoutingExtension::class));
        $this->assertFalse($this->container->hasDefinition(RoutingRuntime::class));
    }

    public function testWithTwig()
    {
        $this->container->register('twig');

        $this->pass->process($this->container);

        $this->assertTrue($this->container->hasDefinition(RoutingExtension::class));
        $this->assertTrue($this->container->hasDefinition(RoutingRuntime::class));

        $options = $this->container->getDefinition(RoutingExtension::class)->getArgument(0);

        $this->assertEquals(['discover_routing_functions' => false], $options);
    }

    public function testWithTwigRoutingExtension()
    {
        $this->container->register('twig');
        $this->container->register('twig.extension.routing');

        $this->pass->process($this->container);

        $options = $this->container->getDefinition(RoutingExtension::class)->getArgument(0);

        $this->assertEquals([], $options);
    }
}
