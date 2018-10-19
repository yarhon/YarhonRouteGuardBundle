<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadataFactory
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * RouteMetadataFactory constructor.
     *
     * @param CacheItemPoolInterface $cache
     * @param RouterInterface        $router
     */
    public function __construct(CacheItemPoolInterface $cache, RouterInterface $router)
    {
        $this->cache = $cache;
        $this->routeCollection = $router->getRouteCollection();
    }

    /**
     * @param string $routeName
     *
     * @return RouteMetadata
     *
     * @throws RuntimeException
     */
    public function createMetadata($routeName)
    {
        $cacheKey = $this->fixCacheKey($routeName);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        if (!$route = $this->routeCollection->get($routeName)) {
            throw new RuntimeException(sprintf('Cannot create RouteMetadata for route "%s" - unknown route.', $routeName));
        }

        $defaults = $route->getDefaults();
        unset($defaults['_canonical_route'], $defaults['_controller']);

        $compiledRoute = $route->compile();
        $variables = $compiledRoute->getVariables();

        $metadata = new RouteMetadata($defaults, $variables, null);

        $cacheItem->set($metadata);
        $this->cache->save($cacheItem);

        return $metadata;
    }

    public function warmUp()
    {
        $this->cache->clear();

        foreach ($this->routeCollection as $name => $route) {
            $this->createMetadata($name);
        }
    }

    /**
     * @see \Symfony\Component\Cache\CacheItem::validateKey
     *
     * @param string $key
     *
     * @return string
     */
    private function fixCacheKey($key)
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '#', $key);
    }
}
