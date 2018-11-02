<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Cache\DataCollector;

use Symfony\Component\Routing\Route;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadataFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;
use Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderAggregate;
use Yarhon\RouteGuardBundle\Exception\ExceptionInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteDataCollector
{
    /**
     * @var TestProviderAggregate
     */
    private $testProvider;

    /**
     * @var ControllerMetadataFactory
     */
    private $controllerMetadataFactory;

    /**
     * @var RouteMetadataFactory
     */
    private $routeMetadataFactory;

    /**
     * @param TestProviderAggregate     $testProvider
     * @param ControllerMetadataFactory $controllerMetadataFactory
     * @param RouteMetadataFactory      $routeMetadataFactory
     */
    public function __construct(TestProviderAggregate $testProvider, ControllerMetadataFactory $controllerMetadataFactory, RouteMetadataFactory $routeMetadataFactory)
    {
        $this->testProvider = $testProvider;
        $this->controllerMetadataFactory = $controllerMetadataFactory;
        $this->routeMetadataFactory = $routeMetadataFactory;
    }

    /**
     * @param string      $routeName
     * @param Route       $route
     * @param string|null $controllerName
     *
     * @return array
     *
     * @throws ExceptionInterface
     */
    public function collect($routeName, Route $route, $controllerName = null)
    {
        $controllerMetadata = $controllerName ? $this->controllerMetadataFactory->createMetadata($controllerName) : null;
        $routeMetadata = $this->routeMetadataFactory->createMetadata($route);

        $tests = $this->testProvider->getTests($routeName, $route, $controllerMetadata);

        return [$tests, $controllerMetadata, $routeMetadata];
    }
}