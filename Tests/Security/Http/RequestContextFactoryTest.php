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
use Symfony\Component\Routing\RequestContext;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\Http\RequestContextFactory;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestContextFactoryTest extends TestCase
{
    private $requestStack;

    private $request;

    private $urlGenerator;

    private $urlGeneratorContext;

    private $factory;

    public function setUp()
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->request = $this->createMock(Request::class);

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->urlGeneratorContext = $this->createMock(RequestContext::class);
        $this->urlGenerator->method('getContext')
            ->willReturn($this->urlGeneratorContext);

        $this->requestStack->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->factory = new RequestContextFactory($this->requestStack, $this->urlGenerator);
    }

    public function testPathInfoClosure()
    {
        $routeContext = new RouteContext('main');

        $context = $this->factory->createContext($routeContext);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeContext->getName(), $routeContext->getParameters(), $routeContext->getReferenceType())
            ->willReturn('http://site.com/foo');

        $this->assertEquals('/foo', $context->getPathInfo());

        $this->assertEquals('http://site.com/foo', $routeContext->getGeneratedUrl());
    }

    public function testPathInfoClosureWithRelativePath()
    {
        $routeContext = new RouteContext('main', [], 'POST', UrlGeneratorInterface::RELATIVE_PATH);

        $context = $this->factory->createContext($routeContext);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeContext->getName(), $routeContext->getParameters(), UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('http://site.com/foo');

        $this->assertEquals('/foo', $context->getPathInfo());

        $this->assertNull($routeContext->getGeneratedUrl());
    }

    public function testPathInfoClosureWithContextBaseUrl()
    {
        $routeContext = new RouteContext('main');

        $context = $this->factory->createContext($routeContext);

        $this->urlGenerator->method('generate')
            ->willReturn('http://site.com/foo');

        $this->urlGeneratorContext->method('getBaseUrl')
            ->willReturn('/foo');

        $this->assertEquals('/', $context->getPathInfo());
        $this->assertEquals('http://site.com/foo', $routeContext->getGeneratedUrl());
    }

    public function testHostClosure()
    {
        $routeContext = new RouteContext('main');

        $context = $this->factory->createContext($routeContext);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeContext->getName(), $routeContext->getParameters(), $routeContext->getReferenceType())
            ->willReturn('http://site.com/foo');

        $this->assertEquals('site.com', $context->getHost());
        $this->assertEquals('http://site.com/foo', $routeContext->getGeneratedUrl());
    }

    public function testHostClosureWithContextHost()
    {
        $routeContext = new RouteContext('main');

        $context = $this->factory->createContext($routeContext);

        $this->urlGenerator->method('generate')
            ->willReturn('/foo');

        $this->urlGeneratorContext->method('getHost')
            ->willReturn('site.com');

        $this->assertEquals('site.com', $context->getHost());
        $this->assertEquals('/foo', $routeContext->getGeneratedUrl());
    }

    public function testMethod()
    {
        $routeContext = new RouteContext('main', [], 'POST');

        $context = $this->factory->createContext($routeContext);

        $this->assertEquals('POST', $context->getMethod());
    }

    public function testClientIp()
    {
        $routeContext = new RouteContext('main');

        $this->request->method('getClientIp')
            ->willReturn('127.0.0.1');

        $context = $this->factory->createContext($routeContext);

        $this->assertEquals('127.0.0.1', $context->getClientIp());
    }
}
