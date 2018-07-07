<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection\Configurator;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\Routing\RouteCollection;
use Yarhon\LinkGuardBundle\Security\AccessMap;
use Yarhon\LinkGuardBundle\Routing\ControllerNameConverter;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapConfigurator
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ControllerNameConverter
     */
    private $controllerNameConverter;

    /**
     * AccessMapConfigurator constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param AccessMap $accessMap
     */
    public function configure(AccessMap $accessMap)
    {
        // TODO: separate test for clone
        $routeCollection = clone $this->router->getRouteCollection();
        $this->convertCollectionControllers($routeCollection);
        $accessMap->setRouteCollection($routeCollection);
    }

    /**
     * @param ControllerNameConverter $controllerNameConverter
     */
    public function setControllerNameConverter(ControllerNameConverter $controllerNameConverter)
    {
        $this->controllerNameConverter = $controllerNameConverter;
    }

    /**
     * @param RouteCollection $collection
     */
    private function convertCollectionControllers(RouteCollection $collection)
    {
        if (!$this->controllerNameConverter) {
            return;
        }

        foreach ($collection as $route) {
            $controller = $route->getDefault('_controller');
            if (2 == count(explode('::', $controller))) {
                continue;
            }

            try {
                $converted = $this->controllerNameConverter->convert($controller);
                $route->setDefault('_controller', $converted);
            } catch (\InvalidArgumentException $e) {
                // TODO: what to do in case of exception
                throw $e;
            }
        }
    }
}
