<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Cache\DataCollector\RouteCollectionDataCollector;
use Yarhon\RouteGuardBundle\Cache\AuthorizationCacheWarmer;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AuthorizationCacheWarmerTest extends TestCase
{
    private $dataCollector;

    private $testsCache;

    private $controllerMetadataCache;

    private $routeMetadataCache;

    private $cacheWarmer;

    public function setUp()
    {
        $this->dataCollector = $this->createMock(RouteCollectionDataCollector::class);

        $router = $this->createMock(RouterInterface::class);

        $router->method('getRouteCollection')
            ->willReturn(new RouteCollection());

        $this->testsCache = new ArrayAdapter(0, false);
        $this->controllerMetadataCache = new ArrayAdapter(0, false);
        $this->routeMetadataCache = new ArrayAdapter(0, false);

        $this->cacheWarmer = new AuthorizationCacheWarmer($this->dataCollector, $router, $this->testsCache, $this->controllerMetadataCache, $this->routeMetadataCache);
    }

    public function testCachesAreFilled()
    {
        $routeOneData = $this->createRouteData();
        $routeTwoData = $this->createRouteData();

        $this->dataCollector->method('collect')
            ->willReturn([
                'route1' => $routeOneData,
                'route2' => $routeTwoData
            ]);

        $this->cacheWarmer->warmUp('');

        $this->assertSame($routeOneData[0], $this->testsCache->getItem('route1')->get());
        $this->assertSame($routeOneData[1], $this->controllerMetadataCache->getItem('route1')->get());
        $this->assertSame($routeOneData[2], $this->routeMetadataCache->getItem('route1')->get());

        $this->assertSame($routeTwoData[0], $this->testsCache->getItem('route2')->get());
        $this->assertSame($routeTwoData[1], $this->controllerMetadataCache->getItem('route2')->get());
        $this->assertSame($routeTwoData[2], $this->routeMetadataCache->getItem('route2')->get());
    }

    public function testCachesAreCleared()
    {
        list($tests, $controllerMetadata, $routeMetadata) = $this->createRouteData();

        $this->addCacheItem($this->testsCache, 'index', $tests);
        $this->addCacheItem($this->controllerMetadataCache, 'index', $controllerMetadata);
        $this->addCacheItem($this->routeMetadataCache, 'index', $routeMetadata);

        $this->dataCollector->method('collect')
            ->willReturn([]);

        $this->cacheWarmer->warmUp('');

        $this->assertFalse($this->testsCache->hasItem('index'));
        $this->assertFalse($this->controllerMetadataCache->hasItem('index'));
        $this->assertFalse($this->routeMetadataCache->hasItem('index'));

    }

    public function testIsOptional()
    {
        $this->assertFalse($this->cacheWarmer->isOptional());
    }

    private function addCacheItem($cache, $name, $value)
    {
        $cacheItem = $cache->getItem($name);
        $cacheItem->set($value);
        $cache->save($cacheItem);
    }

    private function createRouteData()
    {
        return [[], $this->createMock(ControllerMetadata::class), $this->createMock(RouteMetadata::class)];
    }
}
