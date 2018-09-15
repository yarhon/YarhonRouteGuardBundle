<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Http;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Routing\UrlDeferredInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestContextFactory;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestContextFactoryTest extends TestCase
{
    private $requestStack;

    private $request;

    private $urlGenerator;

    private $factory;

    private $routeContext;

    private $urlDeferred;

    public function setUp()
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->request = $this->createMock(Request::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->requestStack->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->factory = new RequestContextFactory($this->requestStack, $this->urlGenerator);

        $this->urlDeferred = $this->createMock(UrlDeferredInterface::class);
        $this->urlDeferred->method('generate')
            ->willReturnSelf();

        $this->routeContext = $this->createMock(RouteContextInterface::class);
        $this->routeContext->method('createUrlDeferred')
            ->willReturn($this->urlDeferred);
    }

    public function testPathInfoClosure()
    {
        $context = $this->factory->createContext($this->routeContext);

        $this->urlDeferred->expects($this->once())
            ->method('generate');

        $this->urlDeferred->expects($this->once())
            ->method('getPathInfo')
            ->willReturn('/foo');

        $this->assertEquals('/foo', $context->getPathInfo());
    }

    public function testHostClosure()
    {
        $context = $this->factory->createContext($this->routeContext);

        $this->urlDeferred->expects($this->once())
            ->method('generate');

        $this->urlDeferred->expects($this->once())
            ->method('getHost')
            ->willReturn('site.com');

        $this->assertEquals('site.com', $context->getHost());
    }

    public function testMethod()
    {
        $this->routeContext->method('getMethod')
            ->willReturn('POST');

        $context = $this->factory->createContext($this->routeContext);

        $this->assertEquals('POST', $context->getMethod());
    }

    public function testClientIp()
    {
        $this->request->method('getClientIp')
            ->willReturn('127.0.0.1');

        $context = $this->factory->createContext($this->routeContext);

        $this->assertEquals('127.0.0.1', $context->getClientIp());
    }
}
