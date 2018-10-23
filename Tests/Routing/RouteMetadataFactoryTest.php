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
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\CompiledRoute;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadataFactoryTest extends TestCase
{
    private $cache;

    private $routeCollection;

    private $factory;

    public function setUp()
    {
        $this->cache = new ArrayAdapter(0, false);

        $this->routeCollection = new RouteCollection();

        $router = $this->createMock(RouterInterface::class);
        $router->method('getRouteCollection')
            ->willReturn($this->routeCollection);

        $this->factory = new RouteMetadataFactory($this->cache, $router);
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

        $this->routeCollection->add('index', $route);

        $metadata = $this->factory->createMetadata('index');

        $this->assertInstanceOf(RouteMetadata::class, $metadata);
        $this->assertEquals(['page' => 1], $metadata->getDefaults());
        $this->assertEquals(['page', 'offset'], $metadata->getVariables());
    }

    public function testCreateMetadataUnknownRouteException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create RouteMetadata for route "index" - unknown route.');

        $this->factory->createMetadata('index');
    }

    public function testCreateMetadataCache()
    {
        $this->routeCollection->add('index', new Route('/'));
        $this->routeCollection->add('blog', new Route('/blog'));

        $metadataOne = $this->factory->createMetadata('index');
        $metadataTwo = $this->factory->createMetadata('index');
        $metadataThree = $this->factory->createMetadata('blog');

        $this->assertSame($metadataOne, $metadataTwo);
        $this->assertNotSame($metadataTwo, $metadataThree);

        $this->assertTrue($this->cache->hasItem('index'));
        $this->assertTrue($this->cache->hasItem('blog'));
    }

    public function testCreateMetadataCacheSpecialSymbols()
    {
        $this->routeCollection->add('blog{}()/\@:', new Route('/'));

        $metadata = $this->factory->createMetadata('blog{}()/\@:');
        $metadataCached = $this->factory->createMetadata('blog{}()/\@:');

        $this->assertInstanceOf(RouteMetadata::class, $metadata);
        $this->assertSame($metadata, $metadataCached);
    }

    public function testWarmUp()
    {
        $this->routeCollection->add('index', new Route('/'));
        $this->routeCollection->add('blog', new Route('/blog'));

        $this->factory->warmUp();

        $this->assertTrue($this->cache->hasItem('index'));
        $this->assertTrue($this->cache->hasItem('blog'));

        $this->assertInstanceOf(RouteMetadata::class, $this->cache->getItem('index')->get());
        $this->assertInstanceOf(RouteMetadata::class, $this->cache->getItem('blog')->get());
    }
}
