<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Yarhon\RouteGuardBundle\Security\Provider\ProviderInterface;
use Yarhon\RouteGuardBundle\Routing\RouteCollection\TransformerInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilder implements AccessMapBuilderInterface, LoggerAwareInterface
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var ProviderInterface[]
     */
    private $authorizationProviders = [];

    /**
     * @var TransformerInterface[]
     */
    private $routeCollectionTransformers = [];

    /**
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache = null)
    {
        $this->cache = $cache ?: new ArrayAdapter();
    }

    /**
     * @param ProviderInterface $provider
     */
    public function addAuthorizationProvider(ProviderInterface $provider)
    {
        $this->authorizationProviders[] = $provider;
    }

    /**
     * @param TransformerInterface $transformer
     */
    public function addRouteCollectionTransformer(TransformerInterface $transformer)
    {
        $this->routeCollectionTransformers[] = $transformer;
    }

    /**
     * @param ProviderInterface[] $providers
     */
    public function setAuthorizationProviders(array $providers)
    {
        $this->authorizationProviders = [];

        foreach ($providers as $provider) {
            $this->addAuthorizationProvider($provider);
        }
    }

    /**
     * @param TransformerInterface[] $transformers
     */
    public function setRouteCollectionTransformers(array $transformers)
    {
        $this->routeCollectionTransformers = [];

        foreach ($transformers as $transformer) {
            $this->addRouteCollectionTransformer($transformer);
        }
    }

    /**
     * @param RouteCollection $routeCollection
     */
    public function setRouteCollection(RouteCollection $routeCollection)
    {
        $this->routeCollection = $routeCollection;
    }

    /**
     * @param RouterInterface $router
     */
    public function importRouteCollection(RouterInterface $router)
    {
        $this->setRouteCollection($router->getRouteCollection());
    }

    ////////  not tested

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        foreach ($this->authorizationProviders as $provider) {
            $provider->setLogger($this->logger);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build($force = false)
    {
        $cacheItem = $this->cache->getItem('map');

        if ($force || null === $accessMap = $cacheItem->get()) {
            $accessMap = $this->doBuild();
            $cacheItem->set($accessMap);
            $this->cache->save($cacheItem);
        } else {
            $this->logger->info('Using cached access map.');
        }

        return $accessMap;
    }

    /**
     * @throws InvalidArgumentException If exception in one of the RouteCollection transformers was thrown
     */
    private function doBuild()
    {
        if (!$this->routeCollection) {
            // TODO: warning or exception
            return;
        }

        if (0 === count($this->authorizationProviders)) {
            // TODO: warning or exception
            return;
        }

        if ($this->logger) {
            $this->logger->info('Build access map. Route collection count', ['count' => count($this->routeCollection)]);
        }

        $originalRoutes = array_keys($this->routeCollection->all());
        $routeCollection = $this->transformRouteCollection($this->routeCollection);
        $ignoredRoutes = array_diff($originalRoutes, array_keys($routeCollection->all()));
        // TODO: check controllers format in case when no ControllerNameTransformer added ?

        if ($this->logger && count($ignoredRoutes)) {
            $this->logger->info('Ignored routes count', ['count' => count($ignoredRoutes)]);
        }

        $this->onBuild();

        $accessMap = new AccessMap();

        foreach ($routeCollection->all() as $name => $route) {
            foreach ($this->authorizationProviders as $provider) {
                $testBag = $provider->getTests($route);
                $accessMap->add($name, get_class($provider), $testBag);
            }
        }

        return $accessMap;
    }

    /**
     * @param RouteCollection $routeCollection
     *
     * @return RouteCollection
     *
     * @throws InvalidArgumentException If exception in one of the RouteCollection transformers was thrown
     */
    private function transformRouteCollection(RouteCollection $routeCollection)
    {
        $routeCollection = clone $routeCollection;

        foreach ($this->routeCollectionTransformers as $transformer) {
            $routeCollection = $transformer->transform($routeCollection);
        }

        return $routeCollection;
    }

    private function onBuild()
    {
        foreach ($this->authorizationProviders as $provider) {
            $provider->onBuild();
        }
    }
}
