<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Cache\DataCollector;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
use Yarhon\RouteGuardBundle\Exception\ExceptionInterface;
use Yarhon\RouteGuardBundle\Cache\DataCollector\RouteDataCollector;
use Yarhon\RouteGuardBundle\Cache\DataCollector\RouteCollectionDataCollector;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteCollectionDataCollectorTest extends TestCase
{
    private $routeDataCollector;

    private $controllerNameResolver;

    private $logger;

    public function setUp()
    {
        $this->routeDataCollector = $this->createMock(RouteDataCollector::class);
        $this->controllerNameResolver = $this->createMock(ControllerNameResolverInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    private function createRouteCollection($routes = [])
    {
        $routeCollection = new RouteCollection();

        foreach ($routes as $path => $controller) {
            $route = new Route($path, ['_controller' => $controller]);
            $routeCollection->add($path, $route);
        }

        return $routeCollection;
    }
}
