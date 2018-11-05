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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Yarhon\RouteGuardBundle\Cache\DataCollector\RouteCollectionDataCollector;
use Yarhon\RouteGuardBundle\Cache\AuthorizationCacheWarmer;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AuthorizationCacheWarmerTest extends TestCase
{
    private $dataCollector;

    private $routeCollection;

    private $testsCache;

    private $controllerMetadataCache;

    private $routeMetadataCache;

    private $cacheWarmer;

    public function setUp()
    {
        $this->dataCollector = $this->createMock(RouteCollectionDataCollector::class);

        $this->routeCollection = new RouteCollection();

        $router = $this->createMock(RouterInterface::class);
        $router->method('getRouteCollection')
            ->willReturn($this->routeCollection);

        $this->testsCache = $this->createMock(CacheItemPoolInterface::class);
        $this->controllerMetadataCache = $this->createMock(CacheItemPoolInterface::class);
        $this->routeMetadataCache = $this->createMock(CacheItemPoolInterface::class);

        $this->cacheWarmer = new AuthorizationCacheWarmer($this->dataCollector, $router, $this->testsCache, $this->controllerMetadataCache, $this->routeMetadataCache);
    }

    public function testIsOptional()
    {
        $this->assertFalse($this->cacheWarmer->isOptional());
    }

    private function createRouteData()
    {
        return [[], $this->createMock(ControllerMetadata::class), $this->createMock(RouteMetadata::class)];
    }
}
