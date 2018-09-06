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
use Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderInterface;
use Yarhon\RouteGuardBundle\Routing\RouteCollection\TransformerInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilder implements LoggerAwareInterface
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var TestProviderInterface[]
     */
    private $testProviders = [];

    /**
     * @var TransformerInterface[]
     */
    private $routeCollectionTransformers = [];

    /**
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * @param TestProviderInterface $provider
     */
    public function addTestProvider(TestProviderInterface $provider)
    {
        $this->testProviders[] = $provider;
    }

    /**
     * @param TransformerInterface $transformer
     */
    public function addRouteCollectionTransformer(TransformerInterface $transformer)
    {
        $this->routeCollectionTransformers[] = $transformer;
    }

    /**
     * @param TestProviderInterface[] $providers
     */
    public function setTestProviders(array $providers)
    {
        $this->testProviders = [];

        foreach ($providers as $provider) {
            $this->addTestProvider($provider);
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

        foreach ($this->testProviders as $provider) {
            $provider->setLogger($this->logger);
        }
    }

    /**
     * @param AccessMap $accessMap
     *
     * @throws InvalidArgumentException            If exception in one of the RouteCollection transformers was thrown
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function build(AccessMap $accessMap)
    {
        if (!$this->routeCollection) {
            // TODO: warning or exception
            return;
        }

        if (0 === count($this->testProviders)) {
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

        foreach ($routeCollection->all() as $name => $route) {
            foreach ($this->testProviders as $provider) {
                $testBag = $provider->getTests($route);
                if (null !== $testBag) {
                    $testBag->setProviderName($provider->getName());
                    $accessMap->add($name, $testBag);
                }
            }
        }
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
        foreach ($this->testProviders as $provider) {
            $provider->onBuild();
        }
    }
}
