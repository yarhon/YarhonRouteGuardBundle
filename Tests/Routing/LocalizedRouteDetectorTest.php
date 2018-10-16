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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Yarhon\RouteGuardBundle\Routing\LocalizedRouteDetector;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class LocalizedRouteDetectorTest extends TestCase
{
    private $routeCollection;

    private $context;

    private $detector;

    public function setUp()
    {
        $this->routeCollection = $this->createMock(RouteCollection::class);
        $this->context = $this->createMock(RequestContext::class);

        $router = $this->createMock(RouterInterface::class);

        $router->method('getRouteCollection')
            ->willReturn($this->routeCollection);

        $router->method('getContext')
            ->willReturn($this->context);

        $this->detector = new LocalizedRouteDetector($router);

        $this->context->method('getParameter')
            ->with('_locale')
            ->willReturn('en');
    }

    public function testByLocaleFromContext()
    {
        $route = $this->createMock(Route::class);

        $route->method('getDefault')
            ->with('_canonical_route')
            ->willReturn('route1');

        $this->routeCollection->method('get')
            ->with('route1.en')
            ->willReturn($route);

        $this->assertEquals('route1.en', $this->detector->getLocalizedName('route1'));
    }

    public function testByLocaleFromParameters()
    {
        $route = $this->createMock(Route::class);

        $route->method('getDefault')
            ->with('_canonical_route')
            ->willReturn('route1');

        $this->routeCollection->method('get')
            ->with('route1.fr')
            ->willReturn($route);

        $this->assertEquals('route1.fr', $this->detector->getLocalizedName('route1', ['_locale' => 'fr']));
    }

    public function testNonLocalized()
    {
        $this->routeCollection->method('get')
            ->with('route1.fr')
            ->willReturn(null);

        $this->assertNull($this->detector->getLocalizedName('route1', ['_locale' => 'fr']));
    }
}
