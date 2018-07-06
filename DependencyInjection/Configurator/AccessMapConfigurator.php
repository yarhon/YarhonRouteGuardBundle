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
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;
use Yarhon\LinkGuardBundle\Security\AccessMap;

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
     * @var KernelInterface
     */
    private $kernel;

    /**
     * AccessMapConfigurator constructor.
     * TODO: think how to get rid from KernelInterface dependency
     *
     * @param RouterInterface $router
     * @param KernelInterface $kernel
     */
    public function __construct(RouterInterface $router, KernelInterface $kernel)
    {
        $this->router = $router;
        $this->kernel = $kernel;
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
     * @param RouteCollection $collection
     */
    private function convertCollectionControllers(RouteCollection $collection)
    {
        foreach ($collection as $route) {
            $controller = $route->getDefault('_controller');
            // TODO: what to do if convert returns null? Maybe throw an exception, as in original method?
            $converted = $this->convertController($controller);
            $route->setDefault('_controller', $converted);
        }
    }

    /**
     * Converts a short notation a:b:c to a class::method.
     *
     * Copied (with minimal changes) from \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser::parse
     * to not add whole symfony/framework-bundle dependency just for the one method usage.
     *
     * @param string $controller A short notation controller (a:b:c)
     *
     * @return string|null A string in the class::method notation or null in case of error
     */
    private function convertController($controller)
    {
        $parts = explode('::', $controller);
        if (2 === count($parts)) {
            // Class::method notation, nothing to do.
            return $controller;
        }

        $parts = explode(':', $controller);
        if (3 !== count($parts) || in_array('', $parts, true)) {
            return null;
        }

        list($bundleName, $controller, $action) = $parts;
        $controller = str_replace('/', '\\', $controller);

        try {
            $bundle = $this->kernel->getBundle($bundleName);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        $try = $bundle->getNamespace().'\\Controller\\'.$controller.'Controller';
        if (class_exists($try)) {
            return $try.'::'.$action.'Action';
        }

        return null;
    }
}
