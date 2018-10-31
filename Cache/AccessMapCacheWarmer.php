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
use Yarhon\RouteGuardBundle\Security\AccessMapBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var AccessMapBuilder
     */
    private $accessMapBuilder;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var CacheItemPoolInterface
     */
    private $authorizationCache;

    /**
     * @var CacheItemPoolInterface
     */
    private $routeMetadataCache;

    /**
     * @var CacheItemPoolInterface
     */
    private $controllerMetadataCache;

    /**
     * AccessMapCacheWarmer constructor.
     *
     * @param AccessMapBuilder $accessMapBuilder
     * @param RouterInterface $router
     * @param CacheItemPoolInterface $authorizationCache
     * @param CacheItemPoolInterface $routeMetadataCache
     * @param CacheItemPoolInterface $controllerMetadataCache
     */
    public function __construct(AccessMapBuilder $accessMapBuilder, RouterInterface $router, CacheItemPoolInterface $authorizationCache, CacheItemPoolInterface $routeMetadataCache, CacheItemPoolInterface $controllerMetadataCache)
    {
        $this->accessMapBuilder = $accessMapBuilder;
        $this->routeCollection = $router->getRouteCollection();

        $this->authorizationCache = $authorizationCache;
        $this->routeMetadataCache = $routeMetadataCache;
        $this->controllerMetadataCache = $controllerMetadataCache;
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

        $accessInfoGenerator = $this->accessMapBuilder->build($this->routeCollection);
        $accessMap = [];

        foreach ($accessInfoGenerator as $routeName => $accessInfo) {
            // TODO: isn't it magic, that generator can return less items than present in initial collection,
            // because of ignored / exception routes?

            // var_dump($routeName);

            list($tests, $routeMetadata, $controllerMetadata) = $accessInfo;

            // !!!! saves even without commit (on destruct) !!!

            $this->saveDeferred($this->authorizationCache, $routeName, $tests);
            $this->saveDeferred($this->routeMetadataCache, $routeName, $routeMetadata);
            $this->saveDeferred($this->controllerMetadataCache, $routeName, $controllerMetadata);
        }

        // $this->commit();
    }

    private function clear()
    {
        $this->authorizationCache->clear();
        $this->routeMetadataCache->clear();
        $this->controllerMetadataCache->clear();
    }

    private function commit()
    {
        $this->authorizationCache->commit();
        $this->routeMetadataCache->commit();
        $this->controllerMetadataCache->commit();
    }

    private function saveDeferred(CacheItemPoolInterface $cache, $routeName, $item)
    {
        $cacheKey = CacheFactory::getValidCacheKey($routeName);

        $cacheItem = $cache->getItem($cacheKey);
        $cacheItem->set($item);
        $cache->saveDeferred($cacheItem);
    }
}