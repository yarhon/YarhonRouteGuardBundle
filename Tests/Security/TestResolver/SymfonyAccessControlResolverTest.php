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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Yarhon\RouteGuardBundle\Security\Http\RequestContextFactory;
use Yarhon\RouteGuardBundle\Security\Http\RequestContext;
use Yarhon\RouteGuardBundle\Security\Test\IsGrantedTest;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Http\RequestDependentTestBag;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\TestProvider\SymfonyAccessControlProvider;
use Yarhon\RouteGuardBundle\Security\TestResolver\SymfonyAccessControlResolver;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlResolverTest extends TestCase
{
    private $request;

    private $requestContext;

    private $resolver;

    private $routeContext;

    public function setUp()
    {
        $requestStack = $this->createMock(RequestStack::class);
        $this->request = $this->createMock(Request::class);

        $requestStack->method('getCurrentRequest')
            ->willReturn($this->request);

        $requestContextFactory = $this->createMock(RequestContextFactory::class);
        $this->requestContext = $this->createMock(RequestContext::class);

        $requestContextFactory->method('createContext')
            ->willReturn($this->requestContext);

        $this->resolver = new SymfonyAccessControlResolver($requestStack, $requestContextFactory);

        $this->routeContext = new RouteContext('main', [], 'POST');
    }

    public function testGetProviderClass()
    {
        $this->assertSame(SymfonyAccessControlProvider::class, $this->resolver->getProviderClass());
    }

    public function testResolveTestBag()
    {
        $test = $this->createMock(IsGrantedTest::class);

        $testBag = $this->createMock(TestBag::class);
        $testBag->method('getTests')
            ->willReturn([$test]);

        $test->expects($this->once())
            ->method('setSubject')
            ->with($this->request);

        $resolved = $this->resolver->resolve($testBag, $this->routeContext);

        $this->assertSame([$test], $resolved);
    }

    public function testResolveRequestDependentTestBagWhenHasMatch()
    {
        $test = $this->createMock(IsGrantedTest::class);

        $testBag = $this->createMock(RequestDependentTestBag::class);
        $testBag->expects($this->once())
            ->method('getTests')
            ->with($this->requestContext)
            ->willReturn([$test]);

        $test->expects($this->once())
            ->method('setSubject')
            ->with($this->request);

        $resolved = $this->resolver->resolve($testBag, $this->routeContext);

        $this->assertSame([$test], $resolved);
    }

    public function testResolveRequestDependentTestBagWhenNotHasMatch()
    {
        $testBag = $this->createMock(RequestDependentTestBag::class);
        $testBag->expects($this->once())
            ->method('getTests')
            ->with($this->requestContext)
            ->willReturn([]);

        $resolved = $this->resolver->resolve($testBag, $this->routeContext);

        $this->assertSame([], $resolved);
    }
}
