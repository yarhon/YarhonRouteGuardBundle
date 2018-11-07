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
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;
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

    public function testCollect()
    {
        $collector = $this->createCollector();

        $this->controllerNameResolver->method('resolve')
            ->willReturnArgument(0);

        $routeData = $this->createRouteData();

        $this->routeDataCollector->method('collect')
            ->willReturn($routeData);

        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path2' => 'class::method2',
        ]);

        $data = $collector->collect($routeCollection);

        $expected = [
            '/path1' => $routeData,
            '/path2' => $routeData,
        ];

        $this->assertSame($expected, $data);
    }

    public function testCollectWithControllerNameResolverException()
    {
        $collector = $this->createCollector();

        $this->controllerNameResolver->method('resolve')
            ->willThrowException(new InvalidArgumentException('Inner exception.'));

        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route "/path1": Inner exception.');

        $collector->collect($routeCollection);
    }

    public function testCollectWithRouteDataCollectorException()
    {
        $collector = $this->createCollector();

        $this->controllerNameResolver->method('resolve')
            ->willReturnArgument(0);

        $this->routeDataCollector->method('collect')
            ->willThrowException(new InvalidArgumentException('Inner exception.'));

        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route "/path1": Inner exception.');

        $collector->collect($routeCollection);
    }

    public function testCollectWithControllerNameResolverExceptionCaught()
    {
        $collector = $this->createCollector(['ignore_exceptions' => true]);
        $collector->setLogger($this->logger);

        $this->controllerNameResolver->method('resolve')
            ->will($this->onConsecutiveCalls(
                $this->throwException(new InvalidArgumentException('Inner exception.')),
                $this->returnArgument(0)
            ));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Route "/path1" would be ignored because of exception caught: Inner exception.');

        $routeData = $this->createRouteData();

        $this->routeDataCollector->method('collect')
            ->with('/path2')
            ->willReturn($routeData);

        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path2' => 'class::method2',
        ]);

        $data = $collector->collect($routeCollection);

        $expected = [
            '/path2' => $routeData,
        ];

        $this->assertSame($expected, $data);
    }

    public function testCollectWithRouteDataCollectorExceptionCaught()
    {
        $collector = $this->createCollector(['ignore_exceptions' => true]);
        $collector->setLogger($this->logger);

        $this->controllerNameResolver->method('resolve')
            ->willReturnArgument(0);

        $routeData = $this->createRouteData();

        $this->routeDataCollector->method('collect')
            ->will($this->onConsecutiveCalls(
                $this->throwException(new InvalidArgumentException('Inner exception.')),
                $this->returnValue($routeData)
            ));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Route "/path1" would be ignored because of exception caught: Inner exception.');

        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path2' => 'class::method2',
        ]);

        $data = $collector->collect($routeCollection);

        $expected = [
            '/path2' => $routeData,
        ];

        $this->assertSame($expected, $data);
    }

    public function testIgnoredControllers()
    {
        $ignoredControllers = [
            'class1',
            'class2::method2',
        ];

        $collector = $this->createCollector(['ignore_controllers' => $ignoredControllers]);

        $this->controllerNameResolver->method('resolve')
            ->willReturnArgument(0);

        $routeData = $this->createRouteData();

        $this->routeDataCollector->method('collect')
            ->willReturn($routeData);

        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class1::method',
            '/path2' => 'class2::method1',
            '/path3' => 'class2::method2',
        ]);

        $data = $collector->collect($routeCollection);

        $expected = [
            '/path2' => $routeData,
        ];

        $this->assertSame($expected, $data);
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

    private function createCollector(array $options = [])
    {
        return new RouteCollectionDataCollector($this->routeDataCollector, $this->controllerNameResolver, $options);
    }

    private function createRouteData()
    {
        return [[], $this->createMock(ControllerMetadata::class), $this->createMock(RouteMetadata::class)];
    }
}
