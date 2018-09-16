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
use Yarhon\RouteGuardBundle\Exception\CatchableExceptionInterface;
use Yarhon\RouteGuardBundle\Exception\LogicException;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

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
    private $options = [];

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
            'throw_exceptions' => true,
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
     * @param AccessMap $accessMap
     *
     * @throws InvalidArgumentException            If exception in one of the RouteCollection transformers was thrown
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function build(AccessMap $accessMap)
    {
        if (!$this->routeCollection) {
            throw new LogicException('Cannot build access map - route collection is not provided.');
        }

        if (0 === count($this->testProviders)) {
            throw new LogicException('Cannot build access map - no test providers is set.');
        }

        if ($this->logger) {
            $this->logger->info('Build access map. Route collection count', ['count' => count($this->routeCollection)]);
        }

        $this->onBuild();

        $ignoredRoutes = [];

        foreach ($this->routeCollection->all() as $name => $route) {

            try {
                $controllerName = $this->getControllerName($route);

                if (null !== $controllerName && $this->isControllerIgnored($controllerName)) {
                    $ignoredRoutes[] = $name;
                    continue;
                }

                $testBags = [];

                foreach ($this->testProviders as $provider) {

                    $testBag = $provider->getTests($route, $controllerName);

                    if (null !== $testBag) {
                        $testBag->setProviderClass(get_class($provider));
                        $testBags[] = $testBag;
                    }
                }

                // empty test bags  ???

                $accessMap->add($name, $testBags);

            } catch (CatchableExceptionInterface $e) {
                if ($this->options['throw_exceptions']) {
                    throw $e;
                }

                //add exception to map
            }


        }

        if ($this->logger && count($ignoredRoutes)) {
            $this->logger->info('Ignored routes count', ['count' => count($ignoredRoutes)]);
        }
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
     * @param TestProviderInterface $provider
     */
    private function addTestProvider(TestProviderInterface $provider)
    {
        $this->testProviders[] = $provider;
    }

    /**
     * @param string $controllerName
     *
     * @return bool
     */
    private function isControllerIgnored($controllerName)
    {
        list($class) = explode('::', $controllerName);

        foreach ($this->options['ignore_controllers'] as $ignored) {
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
