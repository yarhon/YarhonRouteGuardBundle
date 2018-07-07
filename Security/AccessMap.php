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

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMap
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var ProviderInterface[]
     */
    private $providers = [];

    /**
     * AccessMap constructor.

     * @param RouteCollection|null $routeCollection
     */
    public function __construct(RouteCollection $routeCollection = null)
    {
        $this->routeCollection = $routeCollection;
    }

    /**
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * @param RouteCollection $routeCollection
     */
    public function setRouteCollection(RouteCollection $routeCollection)
    {
        $this->routeCollection = $routeCollection;
    }

    public function build()
    {
        foreach ($this->routeCollection->all() as $name => $route) {
            if ('_' == $name[0]) {
                continue;
            }

            $controller = $route->getDefault('_controller');

            if (null === $controller) {
                continue;
            }

            $rules = $this->getRouteRules($route);
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
        foreach ($this->providers as $provider) {
            $rules = array_merge($rules, $provider->getRouteRules($route));
        }

        return $rules;
    }
}