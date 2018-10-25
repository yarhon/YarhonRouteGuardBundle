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
        $accessMap = $this->accessMapBuilder->build($this->routeCollection);

        foreach ($accessMap as $routeName => $accessInfo) {

            if (null === $accessInfo) {
                var_dump($routeName.' $accessInfo is null');
                continue;
            }

            list($tests, $routeMetadata, $controllerMetadata) = $accessInfo;

            var_dump($routeName, $routeMetadata, $controllerMetadata);
        }
    }
}
