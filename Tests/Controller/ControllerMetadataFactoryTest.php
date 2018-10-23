<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadataFactory;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerMetadataFactoryTest extends TestCase
{
    private $cache;

    private $routeCollection;

    private $controllerNameResolver;

    private $argumentMetadataFactory;

    private $factory;

    public function setUp()
    {
        $this->cache = new ArrayAdapter(0, false);

        $this->routeCollection = new RouteCollection();

        $router = $this->createMock(RouterInterface::class);
        $router->method('getRouteCollection')
            ->willReturn($this->routeCollection);

        $this->controllerNameResolver = $this->createMock(ControllerNameResolverInterface::class);
        $this->argumentMetadataFactory = $this->createMock(ArgumentMetadataFactoryInterface::class);

        $this->factory = new ControllerMetadataFactory($this->cache, $router, $this->controllerNameResolver, $this->argumentMetadataFactory);
    }

    public function testCreateMetadata()
    {
        $this->routeCollection->add('index', new Route('/', ['_controller' => 'zxc']));

        $this->controllerNameResolver->method('resolve')
            ->with('zxc')
            ->willReturn('class::method');

        $argumentMetadatas = [
            new ArgumentMetadata('arg1', 'int', false, false, null),
            new ArgumentMetadata('arg2', 'string', false, false, null),
        ];

        $this->argumentMetadataFactory->method('createArgumentMetadata')
            ->with(['class', 'method'])
            ->willReturn($argumentMetadatas);

        $metadata = $this->factory->createMetadata('index');

        $this->assertInstanceOf(ControllerMetadata::class, $metadata);
        $this->assertEquals('class::method', $metadata->getName());
        $this->assertEquals($argumentMetadatas, $metadata->all());
    }

    public function testCreateMetadataNoControllerName()
    {
        $this->routeCollection->add('index', new Route('/', ['_controller' => 'zxc']));

        $this->controllerNameResolver->method('resolve')
            ->with('zxc')
            ->willReturn(null);

        $metadata = $this->factory->createMetadata('index');

        $this->assertNull($metadata);
    }

    public function testCreateMetadataUnknownRouteException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create ControllerMetadata for route "index" - unknown route.');

        $this->factory->createMetadata('index');
    }

    public function testCreateMetadataCache()
    {
        $this->controllerNameResolver->method('resolve')
            ->willReturn('a::b');

        $this->argumentMetadataFactory->method('createArgumentMetadata')
            ->willReturn([]);

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
        $this->controllerNameResolver->method('resolve')
            ->willReturn('a::b');

        $this->argumentMetadataFactory->method('createArgumentMetadata')
            ->willReturn([]);

        $this->routeCollection->add('blog{}()/\@:', new Route('/'));

        $metadata = $this->factory->createMetadata('blog{}()/\@:');
        $metadataCached = $this->factory->createMetadata('blog{}()/\@:');

        $this->assertInstanceOf(ControllerMetadata::class, $metadata);
        $this->assertSame($metadata, $metadataCached);
    }

    public function testWarmUp()
    {
        $this->controllerNameResolver->method('resolve')
            ->willReturn('a::b');

        $this->argumentMetadataFactory->method('createArgumentMetadata')
            ->willReturn([]);

        $this->routeCollection->add('index', new Route('/'));
        $this->routeCollection->add('blog', new Route('/blog'));

        $this->factory->warmUp();

        $this->assertTrue($this->cache->hasItem('index'));
        $this->assertTrue($this->cache->hasItem('blog'));

        $this->assertInstanceOf(ControllerMetadata::class, $this->cache->getItem('index')->get());
        $this->assertInstanceOf(ControllerMetadata::class, $this->cache->getItem('blog')->get());
    }
}
