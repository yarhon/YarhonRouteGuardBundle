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
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\CompiledRoute;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadataTest extends TestCase
{
    public function testGeneral()
    {
        $route = $this->createMock(Route::class);

        $route->method('getDefault')
            ->with('_controller')
            ->willReturn('a::b');

        $route->method('getDefaults')
            ->willReturn(['_controller' => 'a::b', 'page' => 1]);

        $compiledRoute = $this->createMock(CompiledRoute::class);

        $route->method('compile')
            ->willReturn($compiledRoute);

        $compiledRoute->method('getVariables')
            ->willReturn(['page', 'offset']);

        $routeMetadata = new RouteMetadata($route);

        $this->assertEquals('a::b', $routeMetadata->getControllerName());
        $this->assertEquals(['_controller' => 'a::b', 'page' => 1], $routeMetadata->getDefaults());
        $this->assertEquals(['page', 'offset'], $routeMetadata->getVariables());
    }
}
