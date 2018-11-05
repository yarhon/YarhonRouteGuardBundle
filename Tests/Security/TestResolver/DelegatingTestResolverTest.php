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
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Security\Test\SymfonySecurityTest;
use Yarhon\RouteGuardBundle\Security\TestResolver\DelegatingTestResolver;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class DelegatingTestResolverTest extends TestCase
{

    public function setUp()
    {

    }

    public function testSupports()
    {
        $delegatingResolver = new DelegatingTestResolver();

        $test = $this->createMock(TestInterface::class);

        $this->assertTrue($delegatingResolver->supports($test));
    }

    public function testResolve()
    {
        $resolvers = [
            $this->createMock(TestResolverInterface::class),
            $this->createMock(TestResolverInterface::class),
        ];

        $resolvers[0]->method('supports')
            ->willReturn(false);

        $resolvers[1]->method('supports')
            ->willReturn(true);

        $delegatingResolver = new DelegatingTestResolver($resolvers);

        $test = new SymfonySecurityTest(['ROLE_USER']);
        $routeContext = new RouteContext('index');

        $resolvers[1]->expects($this->once())
            ->method('resolve')
            ->with($test, $routeContext)
            ->willReturn(['foo']);

        $this->assertSame(['foo'], $delegatingResolver->resolve($test, $routeContext));
    }

    public function testResolveException()
    {
        $resolvers = [
            $this->createMock(TestResolverInterface::class),
        ];

        $resolvers[0]->method('supports')
            ->willReturn(false);

        $delegatingResolver = new DelegatingTestResolver($resolvers);

        $test = new SymfonySecurityTest(['ROLE_USER']);
        $test->setProviderClass('class1');
        $routeContext = new RouteContext('index');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('No resolver exists for test instance of "%s", provider "class1".', SymfonySecurityTest::class));

        $delegatingResolver->resolve($test, $routeContext);
    }
}
