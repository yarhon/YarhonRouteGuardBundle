<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Yarhon\RouteGuardBundle\Cache\DataCollector\RouteCollectionDataCollector;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var RouteCollectionDataCollector
     */
    private $dataCollector;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var CacheItemPoolInterface
     */
    private $testsCache;

    /**
     * @var CacheItemPoolInterface
     */
    private $controllerMetadataCache;

    /**
     * @var CacheItemPoolInterface
     */
    private $routeMetadataCache;

    /**
     * @param RouteCollectionDataCollector $dataCollector
     * @param RouterInterface              $router
     * @param CacheItemPoolInterface       $testsCache
     * @param CacheItemPoolInterface       $controllerMetadataCache
     * @param CacheItemPoolInterface       $routeMetadataCache
     */
    public function __construct(RouteCollectionDataCollector $dataCollector, RouterInterface $router, CacheItemPoolInterface $testsCache, CacheItemPoolInterface $controllerMetadataCache, CacheItemPoolInterface $routeMetadataCache)
    {
        $this->dataCollector = $dataCollector;
        $this->routeCollection = $router->getRouteCollection();

        $this->testsCache = $testsCache;
        $this->controllerMetadataCache = $controllerMetadataCache;
        $this->routeMetadataCache = $routeMetadataCache;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->clear();

        $accessInfoGenerator = $this->dataCollector->collect($this->routeCollection);
        $accessMap = [];

        foreach ($accessInfoGenerator as $routeName => $accessInfo) {
            // TODO: isn't it magic, that generator can return less items than present in initial collection,
            // because of ignored / exception routes?

            // var_dump($routeName);

            list($tests, $controllerMetadata, $routeMetadata) = $accessInfo;

            // !!!! saves even without commit (on destruct) !!!

            // Note: currently empty arrays (no tests) are also added to testsCache

            $this->saveDeferred($this->testsCache, $routeName, $tests);
            $this->saveDeferred($this->controllerMetadataCache, $routeName, $controllerMetadata);
            $this->saveDeferred($this->routeMetadataCache, $routeName, $routeMetadata);
        }

        // $this->commit();
    }

    private function clear()
    {
        $this->testsCache->clear();
        $this->controllerMetadataCache->clear();
        $this->routeMetadataCache->clear();
    }

    private function commit()
    {
        $this->testsCache->commit();
        $this->controllerMetadataCache->commit();
        $this->routeMetadataCache->commit();
    }

    private function saveDeferred(CacheItemPoolInterface $cache, $routeName, $item)
    {
        $cacheKey = CacheFactory::getValidCacheKey($routeName);

        $cacheItem = $cache->getItem($cacheKey);
        $cacheItem->set($item);
        $cache->saveDeferred($cacheItem);
    }
}