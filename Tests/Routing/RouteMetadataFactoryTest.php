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
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadataFactoryTest extends TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new RouteMetadataFactory();
    }

    public function testCreateMetadata()
    {
        $route = $this->createMock(Route::class);

        $route->method('getDefaults')
            ->willReturn(['_controller' => 'c::d', '_canonical_route' => 'foo', 'page' => 1]);

        $compiledRoute = $this->createMock(CompiledRoute::class);

        $route->method('compile')
            ->willReturn($compiledRoute);

        $compiledRoute->method('getVariables')
            ->willReturn(['page', 'offset']);

        $metadata = $this->factory->createMetadata($route);

        $this->assertInstanceOf(RouteMetadata::class, $metadata);
        $this->assertEquals(['page' => 1], $metadata->getDefaults());
        $this->assertEquals(['page', 'offset'], $metadata->getVariables());
    }
}
