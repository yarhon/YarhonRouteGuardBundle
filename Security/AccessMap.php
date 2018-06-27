<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Security;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use NeonLight\SecureLinksBundle\Security\Provider\ProviderInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMap
{
    /**
     * @var Route[]
     */
    private $routes;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ProviderInterface[]
     */
    private $providers = [];

    /**
     * AccessMap constructor.
     *
     * @param RouterInterface $router
     * @param KernelInterface $kernel
     */
    public function __construct(RouterInterface $router, KernelInterface $kernel)
    {
        $this->routes = $router->getRouteCollection()->all();
        $this->kernel = $kernel;
    }

    /**
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    public function build()
    {
        foreach ($this->routes as $name => $route) {
            if ('_' == $name[0]) {
                continue;
            }

            $controller = $route->getDefault('_controller');
            $controller = $this->convertController($controller);

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
