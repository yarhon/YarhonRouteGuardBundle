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
use Yarhon\LinkGuardBundle\Security\Provider\ProviderInterface;
use Yarhon\LinkGuardBundle\Controller\ControllerNameResolverInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilder
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var ControllerNameResolverInterface
     */
    private $controllerNameResolver;

    /**
     * @var ProviderInterface[]
     */
    private $authorizationProviders = [];

    /**
     * AccessMapBuilder constructor.
     *
     * @param RouteCollection|null $routeCollection
     */
    public function __construct(RouteCollection $routeCollection = null)
    {
        /* We need to allow $routeCollection accept null value for container to be able to instantiate AccessMapBuilder
           without $routeCollection (it would be set by the configurator) */
        if ($routeCollection) {
            $this->setRouteCollection($routeCollection);
        }
    }

    /**
     * @param ProviderInterface $provider
     */
    public function addAuthorizationProvider(ProviderInterface $provider)
    {
        $this->authorizationProviders[] = $provider;
    }

    /**
     * @param RouteCollection $routeCollection
     *
     * @throws \InvalidArgumentException If unable to resolve controller name (when ControllerNameResolver is set)
     *                                   If controller name is not a string in the class::method notation or boolean false
     */
    public function setRouteCollection(RouteCollection $routeCollection)
    {
        $routeCollection = clone $routeCollection;
        $this->resolveControllers($routeCollection);
        $this->checkControllers($routeCollection);
        $this->routeCollection = $routeCollection;
    }

    /**
     * @param ControllerNameResolverInterface $controllerNameResolver
     */
    public function setControllerNameResolver($controllerNameResolver)
    {
        $this->controllerNameResolver = $controllerNameResolver;
    }

    public function build()
    {
        foreach ($this->routeCollection->all() as $name => $route) {

            $controller = $route->getDefault('_controller');

            // $rules = $this->getRouteRules($route);
        }
    }

    /**
     * @param Route $route
     *
     * @return array
     */
    private function getRouteRules(Route $route)
    {
        $rules = [];
        foreach ($this->authorizationProviders as $provider) {
            $testBag = $provider->getTests($route);
        }

        return $rules;
    }

    /**
     * @param RouteCollection $collection
     *
     * @throws \InvalidArgumentException If unable to resolve controller name (when ControllerNameResolver is set)
     */
    private function resolveControllers(RouteCollection $collection)
    {
        if (!$this->controllerNameResolver) {
            return;
        }

        foreach ($collection as $name => $route) {
            $controller = $route->getDefault('_controller');

            try {
                // Note: for some controllers (i.e, functions as controllers), $controllerName would be false
                $controllerName = $this->controllerNameResolver->resolve($controller);

                if (true) {
                    $route->setDefault('_controller', $controllerName);
                } else {
                    $collection->remove($name);
                }

            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(sprintf('Unable to resolve controller name for route "%s": %s',
                    $name, $e->getMessage()), 0, $e);
            }
        }
    }

    /**
     * @param RouteCollection $collection
     *
     * @throws \InvalidArgumentException If controller name is not a string in the class::method notation or boolean false
     */
    private function checkControllers(RouteCollection $collection)
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
}