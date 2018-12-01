<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\TestBagResolver;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\Http\RequestContextFactory;
use Yarhon\RouteGuardBundle\Security\Http\RequestContext;
use Yarhon\RouteGuardBundle\Security\Http\RequestDependentTestBagInterface;
use Yarhon\RouteGuardBundle\Security\TestBagResolver\TestBagResolver;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagResolverTest extends TestCase
{
    private $requestContextFactory;

    private $resolver;

    public function setUp()
    {
        $this->requestContextFactory = $this->createMock(RequestContextFactory::class);

        $this->resolver = new TestBagResolver($this->requestContextFactory);
    }

    public function testResolveTestBag()
    {
        $tests = [
            $this->createMock(TestInterface::class),
        ];

        $testBag = $this->createMock(TestBagInterface::class);
        $testBag->method('getTests')
            ->willReturn($tests);

        $routeContext = new RouteContext('index');

        $resolved = $this->resolver->resolve($testBag, $routeContext);

        $this->assertSame($tests, $resolved);
    }

    public function testResolveRequestDependentTestBag()
    {
        $tests = [
            $this->createMock(TestInterface::class),
        ];

        $requestContext = new RequestContext('/');

        $this->requestContextFactory->method('createContext')
            ->willReturn($requestContext);

        $testBag = $this->createMock(RequestDependentTestBagInterface::class);
        $testBag->method('getTests')
            ->with($requestContext)
            ->willReturn($tests);

        $routeContext = new RouteContext('index');

        $resolved = $this->resolver->resolve($testBag, $routeContext);

        $this->assertSame($tests, $resolved);
    }

    public function testNoResolverException()
    {
        $testBag = $this->createMock(AbstractTestBagInterface::class);

        $routeContext = new RouteContext('index');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('No resolver exists for test bag instance of "%s".', get_class($testBag)));

        $this->resolver->resolve($testBag, $routeContext);
    }
}
