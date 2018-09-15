<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
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
     * @var ControllerNameResolverInterface
     */
    private $controllerNameResolver;

    /**
     * @var string[]
     */
    private $ignoredControllers = [];

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

    public function setControllerNameResolver(ControllerNameResolverInterface $resolver)
    {
        $this->controllerNameResolver = $resolver;
    }

    /**
     * @param string[] $ignoredControllers
     */
    public function setIgnoredControllers($ignoredControllers)
    {
        $this->ignoredControllers = $ignoredControllers;
    }


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

        $ignoredRoutes = [];

        if ($this->logger && count($ignoredRoutes)) {
            $this->logger->info('Ignored routes count', ['count' => count($ignoredRoutes)]);
        }

        $this->onBuild();

        foreach ($this->routeCollection->all() as $name => $route) {
            foreach ($this->testProviders as $provider) {
                $controllerName = $route->getDefault('_controller');
                $testBag = $provider->getTests($route, $controllerName);
                if (null !== $testBag) {
                    $testBag->setProviderClass(get_class($provider));
                    $accessMap->add($name, $testBag);
                }
            }
        }
    }

    /**
     * @param Route $route
     *
     * @return string|false
     */
    private function getControllerName(Route $route)
    {
        $controller = $route->getDefault('_controller');

        if ($this->controllerNameResolver) {
            $controller = $this->controllerNameResolver->resolve($controller);
        }

        // TODO: check controllers format in case when no resolver added ?

        return $controller;
    }

    /**
     * @param string $controllerName
     *
     * @return bool
     */
    private function isControllerIgnored($controllerName)
    {
        list($class) = explode('::', $controllerName);

        foreach ($this->ignoredControllers as $ignored) {
            if (0 === strpos($class, $ignored)) {
                return true;
            }
        }

        return false;
    }

    private function onBuild()
    {
        foreach ($this->testProviders as $provider) {
            $provider->onBuild();
        }
    }
}
