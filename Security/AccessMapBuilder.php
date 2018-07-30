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
     * @var TransformerInterface[]
     */
    private $routeCollectionTransformers = [];

    /**
     * @var string[]
     */
    private $ignoredRoutes = [];

    /**
     * AccessMapBuilder constructor.
     *
     * We need to allow $routeCollection accept null value for container to be able to instantiate AccessMapBuilder
     * without $routeCollection (it would be set by the configurator).
     *
     * @param RouteCollection|null $routeCollection
     */
    public function __construct(RouteCollection $routeCollection = null)
    {
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
    public function addRouteCollectionTransformer(TransformerInterface $transformer)
    {
        $this->routeCollectionTransformers[] = $transformer;
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
     *
     * @throws \InvalidArgumentException If exception in one of the RouteCollection transformers was thrown
     */
    public function setRouteCollection(RouteCollection $routeCollection)
    {
        $originalRoutes = array_keys($routeCollection->all());

        $routeCollection = $this->transformRouteCollection($routeCollection);

        $this->routeCollection = $routeCollection;
        $this->ignoredRoutes = array_diff($originalRoutes, array_keys($routeCollection->all()));

        // TODO: check controllers format in case when there no ControllerNameTransformer added ?
        // checkControllersFormat($routeCollection);
    }

    public function build()
    {
        //var_dump($this->routeCollection->all());

        foreach ($this->routeCollection->all() as $name => $route) {
            var_dump($name);

            foreach ($this->authorizationProviders as $provider) {
                $testBag = $provider->getTests($route);
            }
        }
    }

    /**
     * @param RouteCollection $routeCollection
     *
     * @return RouteCollection
     *
     * @throws \InvalidArgumentException If exception in one of the RouteCollection transformers was thrown
     */
    private function transformRouteCollection(RouteCollection $routeCollection)
    {
        $routeCollection = clone $routeCollection;

        foreach ($this->routeCollectionTransformers as $transformer) {
            $routeCollection = $transformer->transform($routeCollection);
        }

        return $routeCollection;
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
