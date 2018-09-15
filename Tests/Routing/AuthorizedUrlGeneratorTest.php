<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Routing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Yarhon\RouteGuardBundle\Routing\AuthorizedUrlGenerator;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\RouteAuthorizationCheckerInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AuthorizedUrlGeneratorTest extends TestCase
{
    private $generatorDelegate;

    private $generatorDelegateContext;

    private $authorizationChecker;

    private $routeCollection;

    private $generator;

    public function setUp()
    {
        $this->generatorDelegate = $this->createMock(UrlGeneratorInterface::class);
        $this->generatorDelegateContext = $this->createMock(RequestContext::class);
        $this->authorizationChecker = $this->createMock(RouteAuthorizationCheckerInterface::class);

        $this->generatorDelegate->method('getContext')
            ->willReturn($this->generatorDelegateContext);

        $this->generatorDelegateContext->method('getParameter')
            ->with('_locale')
            ->willReturn('en');

        $this->routeCollection = $this->createMock(RouteCollection::class);

        $router = $this->createMock(RouterInterface::class);

        $router->method('getRouteCollection')
            ->willReturn($this->routeCollection);

        $this->generator = new AuthorizedUrlGenerator($this->generatorDelegate, $this->authorizationChecker, $router);
    }

    public function testGenerateRouteContext()
    {
        $arguments = ['route1', ['page' => 1], 'POST', UrlGeneratorInterface::RELATIVE_PATH];

        $expectedRouteContext = new RouteContext(...$arguments);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with($expectedRouteContext);

        $this->generator->generate(...$arguments);
    }

    public function testGenerateNotAuthorized()
    {
        $this->authorizationChecker->method('isGranted')
            ->willReturn(false);

        $this->assertFalse($this->generator->generate('route1'));
    }

    public function testGenerateAuthorized()
    {
        $this->authorizationChecker->method('isGranted')
            ->willReturn(true);

        $this->generatorDelegate->expects($this->once())
            ->method('generate')
            ->with('route1', ['page' => 1], UrlGeneratorInterface::RELATIVE_PATH)
            ->willReturn('/url1');

        $this->assertEquals('/url1', $this->generator->generate('route1', ['page' => 1], 'POST', UrlGeneratorInterface::RELATIVE_PATH));
    }

    public function testUrlDeferredWithoutUrlGenerated()
    {
        $this->authorizationChecker->method('isGranted')
            ->willReturnCallback(function ($routeContext) {
                $routeContext->createUrlDeferred();

                return true;
            });

        $this->generatorDelegate->expects($this->once())
            ->method('generate');

        $this->generator->generate('route1');
    }

    public function testUrlDeferredWithUrlGenerated()
    {
        $this->authorizationChecker->method('isGranted')
            ->willReturnCallback(function ($routeContext) {
                $urlDeferred = $routeContext->createUrlDeferred();

                $r = new \ReflectionProperty($urlDeferred, 'generatedUrl');
                $r->setAccessible(true);
                $r->setValue($urlDeferred, '/deferred_generated_url');

                return true;
            });

        $this->generatorDelegate->expects($this->never())
            ->method('generate');

        $this->assertEquals('/deferred_generated_url', $this->generator->generate('route1'));
    }

    public function testLocalizedRouteByLocaleFromContext()
    {
        $route = $this->createMock(Route::class);

        $route->method('getDefault')
            ->with('_canonical_route')
            ->willReturn('route1');

        $this->routeCollection->method('get')
            ->with('route1.en')
            ->willReturn($route);

        $expectedRouteContext = new RouteContext('route1.en');

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with($expectedRouteContext);

        $this->generator->generate('route1');
    }

    public function testLocalizedRouteByLocaleFromParameters()
    {
        $route = $this->createMock(Route::class);

        $route->method('getDefault')
            ->with('_canonical_route')
            ->willReturn('route1');

        $this->routeCollection->method('get')
            ->with('route1.fr')
            ->willReturn($route);

        $expectedRouteContext = new RouteContext('route1.fr');

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with($expectedRouteContext);

        $this->generator->generate('route1', ['_locale' => 'fr']);
    }
}
