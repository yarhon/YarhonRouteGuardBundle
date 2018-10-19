<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerMetadataFactory
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
     * @var ControllerNameResolverInterface
     */
    private $controllerNameResolver;

    /**
     * @var ArgumentMetadataFactoryInterface
     */
    private $argumentMetadataFactory;

    /**
     * ControllerMetadataFactory constructor.
     *
     * @param CacheItemPoolInterface                $cache
     * @param RouterInterface                       $router
     * @param ControllerNameResolverInterface       $controllerNameResolver
     * @param ArgumentMetadataFactoryInterface|null $argumentMetadataFactory
     */
    public function __construct(CacheItemPoolInterface $cache, RouterInterface $router, ControllerNameResolverInterface $controllerNameResolver, ArgumentMetadataFactoryInterface $argumentMetadataFactory = null)
    {
        $this->cache = $cache;
        $this->routeCollection = $router->getRouteCollection();
        $this->controllerNameResolver = $controllerNameResolver;
        $this->argumentMetadataFactory = $argumentMetadataFactory ?: new ArgumentMetadataFactory();
    }

    /**
     * @param string $routeName
     *
     * @return ControllerMetadata|null
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
            throw new RuntimeException(sprintf('Cannot create ControllerMetadata for route "%s" - unknown route.', $routeName));
        }

        $controller = $route->getDefault('_controller');

        // TODO: check exceptions here?
        $controllerName = $this->controllerNameResolver->resolve($controller);

        if (null === $controllerName) {
            return null;
        }

        list($class, $method) = explode('::', $controllerName);
        $arguments = $this->argumentMetadataFactory->createArgumentMetadata([$class, $method]);

        $metadata = new ControllerMetadata($controllerName, $arguments);

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
