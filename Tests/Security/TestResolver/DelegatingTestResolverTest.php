<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\TestResolver;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;
use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\TestResolver\DelegatingTestResolver;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class DelegatingTestResolverTest extends TestCase
{
    private $test;

    private $routeContext;

    public function setUp()
    {
        $this->test = $this->createMock(TestInterface::class);
        $this->test->method('getProviderClass')
            ->willReturn('class_two');

        $this->routeContext = $this->createMock(RouteContextInterface::class);
    }

    public function testGetProviderClass()
    {
        $delegatingResolver = new DelegatingTestResolver();

        $this->assertEquals('', $delegatingResolver->getProviderClass());
    }

    public function testResolve()
    {
        $resolvers = [
            $this->createMock(TestResolverInterface::class),
            $this->createMock(TestResolverInterface::class),
        ];

        $resolvers[0]->method('getProviderClass')
            ->willReturn('class_one');

        $resolvers[1]->method('getProviderClass')
            ->willReturn('class_two');

        $delegatingResolver = new DelegatingTestResolver($resolvers);

        $resolvers[1]->expects($this->once())
            ->method('resolve')
            ->with($this->test, $this->routeContext)
            ->willReturn(['foo']);

        $this->assertSame(['foo'], $delegatingResolver->resolve($this->test, $this->routeContext));
    }

    public function testResolveException()
    {
        $resolvers = [
            $this->createMock(TestResolverInterface::class),
        ];

        $resolvers[0]->method('getProviderClass')
            ->willReturn('class_one');

        $delegatingResolver = new DelegatingTestResolver($resolvers);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resolver exists for provider "class_two".');

        $delegatingResolver->resolve($this->test, $this->routeContext);
    }
}
