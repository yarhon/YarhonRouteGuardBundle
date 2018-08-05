<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Yarhon\LinkGuardBundle\Security\Provider\ProviderInterface;
use Yarhon\LinkGuardBundle\Routing\RouteCollection\TransformerInterface;
use Yarhon\LinkGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
        foreach ($providers as $provider) {
            $this->addAuthorizationProvider($provider);
        }
    }

    /**
     * @param TransformerInterface[] $transformers
     */
    public function setRouteCollectionTransformers(array $transformers)
    {
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

    /**
     * @throws InvalidArgumentException If exception in one of the RouteCollection transformers was thrown
     */
    public function build()
    {
        //var_dump($this->routeCollection->all());

        if (!$this->routeCollection) {
            return;
        }

        $this->injectLogger();

        if ($this->logger) {
            $this->logger->info('Build access map');
            $this->logger->info('Route collection count', ['count' => count($this->routeCollection)]);
        }

        /////////////////////////////////////
        $originalRoutes = array_keys($this->routeCollection->all());
        $routeCollection = $this->transformRouteCollection($this->routeCollection);
        $ignoredRoutes = array_diff($originalRoutes, array_keys($routeCollection->all()));
        // TODO: check controllers format in case when no ControllerNameTransformer added ? $this->checkControllersFormat($routeCollection)
        ////////////////////////////////////

        if ($this->logger) {
            $this->logger->info('Ignored routes count', ['count' => count($ignoredRoutes)]);
        }

        foreach ($routeCollection->all() as $name => $route) {
            foreach ($this->authorizationProviders as $provider) {
                $testBag = $provider->getTests($route);
            }
        }

        // For tests compatibility
        $this->routeCollection = $routeCollection;
        $this->ignoredRoutes = $ignoredRoutes;
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

    private function injectLogger()
    {
        if (!$this->logger) {
            return;
        }

        foreach ($this->authorizationProviders as $provider) {
            $provider->setLogger($this->logger);
        }
    }

    /*
     * @param RouteCollection $collection
     *
     * @throws \InvalidArgumentException If controller name is not a string in the class::method notation or boolean false

    public function checkControllersFormat(RouteCollection $collection)
    {

        foreach ($collection as $name => $route) {
            $controller = $route->getDefault('_controller');

            if (false === $controller) {
                continue;
            }

            if (is_string($controller)) {
                $parts = explode('::', $controller);
                if (2 == count($parts) && !in_array('', $parts, true)) {
                    continue;
                }
            }

            throw new \InvalidArgumentException(
                sprintf('Invalid controller name for route "%s" - it should be either string in the class::method notation or boolean false.', $name)
            );
        }
    }
    */
}
