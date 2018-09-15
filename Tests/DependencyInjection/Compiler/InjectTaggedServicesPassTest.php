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
use Yarhon\RouteGuardBundle\DependencyInjection\Compiler\InjectTaggedServicesPass;
use Yarhon\RouteGuardBundle\Security\AccessMapBuilder;
use Yarhon\RouteGuardBundle\Security\TestResolver\DelegatingTestResolver;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class InjectTaggedServicesPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var InjectTaggedServicesPass
     */
    private $pass;

    public function setUp()
    {
        $this->container = new ContainerBuilder();

        $this->container->register(AccessMapBuilder::class)
            ->addMethodCall('setRouteCollectionTransformers', [[]])
            ->addMethodCall('setTestProviders', [[]]);

        $this->container->register(ControllerArgumentResolver::class)->setArguments([null, []]);

        $this->container->register(DelegatingTestResolver::class)->addArgument([]);

        $this->pass = new InjectTaggedServicesPass();
    }

    /**
     * @dataProvider injectMethodCallsDataProvider
     */
    public function testInjectMethodCalls($destination, $tagName)
    {
        $this->container->register('test1')->addTag($tagName, ['priority' => 10]);
        $this->container->register('test2')->addTag($tagName, ['priority' => 20]);

        $this->pass->process($this->container);

        $definition = $this->container->getDefinition($destination[0]);
        $methodCalls = $definition->getMethodCalls();

        $target = null;

        foreach ($methodCalls as $methodCall) {
            if ($methodCall[0] == $destination[1]) {
                $target = $methodCall;
            }
        }

        $this->assertInternalType('array', $target);

        list($methodName, $arguments) = $target;

        $this->assertEquals($destination[1], $methodName);
        $this->assertCount(1, $arguments);

        $argument = $arguments[0];
        $this->assertInternalType('array', $argument);

        $this->assertEquals('test2', (string) $argument[0]);
        $this->assertEquals('test1', (string) $argument[1]);
    }

    public function injectMethodCallsDataProvider()
    {
        return [
            [
                [AccessMapBuilder::class, 'setTestProviders'],
                'yarhon_route_guard.test_provider',
            ],
        ];
    }

    /**
     * @dataProvider injectArgumentsDataProvider
     */
    public function testInjectArguments($destination, $tagName)
    {
        $this->container->register('test1')->addTag($tagName, ['priority' => 10]);
        $this->container->register('test2')->addTag($tagName, ['priority' => 20]);

        $this->pass->process($this->container);

        $definition = $this->container->getDefinition($destination[0]);

        $argument = $definition->getArgument($destination[1]);

        $this->assertInternalType('array', $argument);

        $this->assertEquals('test2', (string) $argument[0]);
        $this->assertEquals('test1', (string) $argument[1]);
    }

    public function injectArgumentsDataProvider()
    {
        return [
            [
                [ControllerArgumentResolver::class, 1],
                'yarhon_route_guard.argument_value_resolver',
            ],
            [
                [DelegatingTestResolver::class, 0],
                'yarhon_route_guard.test_resolver',
            ],
        ];
    }
}
