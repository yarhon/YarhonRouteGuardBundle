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
use Yarhon\LinkGuardBundle\Routing\RouteCollection\TransformerInterface;
use Yarhon\LinkGuardBundle\Routing\RouteCollection\Transformer;

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
     * @var ProviderInterface[]
     */
    private $authorizationProviders = [];

    /**
     * @var TransformerInterface
     */
    private $routeCollectionTransformer;

    /**
     * @var string[]
     */
    private $ignoredRoutes = [];

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
     * @param TransformerInterface $transformer
     */
    public function setRouteCollectionTransformer(TransformerInterface $transformer)
    {
        $this->routeCollectionTransformer = $transformer;
    }

    /**
     * @param RouteCollection $routeCollection
     *
     * @throws \InvalidArgumentException If unable to resolve controller name (when ControllerNameResolver is set)
     *                                   If controller name is not a string in the class::method notation or boolean false
     */
    public function setRouteCollection(RouteCollection $routeCollection)
    {
        if ($this->routeCollectionTransformer) {
            $routeCollection = $this->routeCollectionTransformer->transform($routeCollection);
            $this->ignoredRoutes = $this->routeCollectionTransformer->getIgnoredRoutes();
        }

        Transformer::checkControllersFormat($routeCollection);
        $this->routeCollection = $routeCollection;
    }

    public function build()
    {
        foreach ($this->routeCollection->all() as $name => $route) {

            $controller = $route->getDefault('_controller');

            var_dump($name, $controller);
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
}