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
    use CacheKeyTrait;

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
            if (null === $accessInfo) {
                continue;
            }

            $accessMap[$routeName] = $accessInfo;
        }

        foreach ($accessMap as $routeName => $accessInfo) {
            $this->save($routeName, $accessInfo);
        }
    }

    private function clear()
    {
        $this->authorizationCache->clear();
        $this->routeMetadataCache->clear();
        $this->controllerMetadataCache->clear();
    }

    /**
     * @param string $routeName
     * @param array  $accessInfo
     */
    private function save($routeName, $accessInfo)
    {
        $cacheKey = $this->getValidCacheKey($routeName);

        list($tests, $routeMetadata, $controllerMetadata) = $accessInfo;

        $cacheItem = $this->authorizationCache->getItem($cacheKey);
        $cacheItem->set($tests);
        $this->authorizationCache->save($cacheItem);

        $cacheItem = $this->routeMetadataCache->getItem($cacheKey);
        $cacheItem->set($routeMetadata);
        $this->routeMetadataCache->save($cacheItem);

        $cacheItem = $this->controllerMetadataCache->getItem($cacheKey);
        $cacheItem->set($controllerMetadata);
        $this->controllerMetadataCache->save($cacheItem);
    }
}