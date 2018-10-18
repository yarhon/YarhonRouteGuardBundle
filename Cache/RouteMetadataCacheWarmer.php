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
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadataCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var RouteMetadataFactory
     */
    private $routeMetadataFactory;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * RouteMetadataCacheWarmer constructor.
     *
     * @param RouteMetadataFactory   $routeMetadataFactory
     * @param CacheItemPoolInterface $cache
     * @param RouterInterface        $router
     */
    public function __construct(RouteMetadataFactory $routeMetadataFactory, CacheItemPoolInterface $cache, RouterInterface $router)
    {
        $this->routeMetadataFactory = $routeMetadataFactory;
        $this->cache = $cache;
        $this->routeCollection = $router->getRouteCollection();
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
        $this->cache->clear();

        foreach ($this->routeCollection as $name => $route) {
            $routeMetadata = $this->routeMetadataFactory->createMetadata($route);

            $cacheItem = $this->cache->getItem($name);
            $cacheItem->set($routeMetadata);
            $this->cache->save($cacheItem);
        }
    }
}
