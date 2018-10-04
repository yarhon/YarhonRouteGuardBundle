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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestContextFactory;
use Yarhon\RouteGuardBundle\Security\Http\RequestContext;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMap;
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

    private $testBag;

    private $testArguments;

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

        $this->testArguments = $this->createMock(TestArguments::class);
        $this->testBag = $this->createMock(TestBag::class);

        $this->testBag->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->testArguments]));

        $this->routeContext = new RouteContext('main', [], 'POST', UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    public function testGetProviderClass()
    {
        $this->assertSame(SymfonyAccessControlProvider::class, $this->resolver->getProviderClass());
    }

    public function testResolveTestBag()
    {
        $this->testArguments->expects($this->once())
            ->method('setSubject')
            ->with($this->request);

        $resolved = $this->resolver->resolve($this->testBag, $this->routeContext);

        $this->assertSame([$this->testArguments], $resolved);
    }

    public function testResolveTestBagMapWhenHasMatch()
    {
        $testBagMap = $this->createMock(TestBagMap::class);
        $testBagMap->expects($this->once())
            ->method('resolve')
            ->with($this->requestContext)
            ->willReturn($this->testBag);

        $this->testArguments->expects($this->once())
            ->method('setSubject')
            ->with($this->request);

        $resolved = $this->resolver->resolve($testBagMap, $this->routeContext);

        $this->assertSame([$this->testArguments], $resolved);
    }

    public function testResolveTestBagMapWhenNotHasMatch()
    {
        $testBagMap = $this->createMock(TestBagMap::class);
        $testBagMap->expects($this->once())
            ->method('resolve')
            ->with($this->requestContext)
            ->willReturn(null);

        $resolved = $this->resolver->resolve($testBagMap, $this->routeContext);

        $this->assertSame([], $resolved);
    }
}
