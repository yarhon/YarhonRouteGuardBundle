<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
use Yarhon\RouteGuardBundle\Exception\CatchableExceptionInterface;
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilder implements LoggerAwareInterface
{
    /**
     * @var TestProviderInterface[]
     */
    private $testProviders = [];

    /**
     * @var array
     */
    private $options;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var ControllerNameResolverInterface
     */
    private $controllerNameResolver;

    /**
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * AccessMapBuilder constructor.
     *
     * @param \Traversable|TestProviderInterface[] $testProviders
     * @param array                                $options
     */
    public function __construct($testProviders = [], array $options = [])
    {
        foreach ($testProviders as $testProvider) {
            $this->addTestProvider($testProvider);
        }

        $this->options = array_merge([
            'ignore_controllers' => [],
            'catch_exceptions' => false,
        ], $options);
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
     * @param ControllerNameResolverInterface $resolver
     */
    public function setControllerNameResolver(ControllerNameResolverInterface $resolver)
    {
        $this->controllerNameResolver = $resolver;
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
     * @param AccessMapInterface $accessMap
     *
     * @throws ???
     */
    public function build(AccessMapInterface $accessMap)
    {
        if (!$this->routeCollection) {
            throw new LogicException('Cannot build access map - route collection is not provided.');
        }

        if (0 === count($this->testProviders)) {
            throw new LogicException('Cannot build access map - no test providers are set.');
        }

        if ($this->logger) {
            $this->logger->info('Build access map. Route collection count', ['count' => count($this->routeCollection)]);
        }

        $accessMap->clear();

        $ignoredRoutes = [];
        $exceptionRoutes = [];

        foreach ($this->routeCollection as $routeName => $route) {
            try {
                $controllerName = $this->getControllerName($route);

                if (null !== $controllerName && $this->isControllerIgnored($controllerName)) {
                    $ignoredRoutes[] = $routeName;
                    continue;
                }

                $testBags = [];

                foreach ($this->testProviders as $provider) {
                    $testBag = $provider->getTests($route, $routeName, $controllerName);

                    if (null !== $testBag) {
                        $testBag->setProviderClass(get_class($provider));
                        $testBags[] = $testBag;
                    }
                }

                // Note: empty arrays are also added to access map
                $result = $accessMap->set($routeName, $testBags);
                // TODO: check set result
            } catch (CatchableExceptionInterface $e) {
                if (!$this->options['catch_exceptions']) {
                    throw $e;
                }

                $exceptionRoutes[] = $routeName;

                if ($this->logger) {
                    $this->logger->error(sprintf('Exception caught while processing route "%s": %s', $routeName, $e->getMessage()), ['exception' => $e]);
                }
            }
        }

        if ($this->logger) {
            if (count($ignoredRoutes)) {
                $this->logger->info('Ignored routes count', ['count' => count($ignoredRoutes)]);
            }
        }
    }

    /**
     * @param TestProviderInterface $provider
     */
    private function addTestProvider(TestProviderInterface $provider)
    {
        $this->testProviders[] = $provider;
    }

    /**
     * @param Route $route
     *
     * @return string|null
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
        foreach ($this->options['ignore_controllers'] as $ignored) {
            if (0 === strpos($controllerName, $ignored)) {
                return true;
            }
        }

        return false;
    }
}
