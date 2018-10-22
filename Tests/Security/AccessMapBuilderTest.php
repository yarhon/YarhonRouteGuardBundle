<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Yarhon\RouteGuardBundle\Security\AccessMapBuilder;
use Yarhon\RouteGuardBundle\Security\AccessMapInterface;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
use Yarhon\RouteGuardBundle\Exception\LogicException;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilderTest extends TestCase
{
    private $providers;

    private $controllerNameResolver;

    private $logger;

    private $accessMap;

    public function setUp()
    {
        $providerOne = $this->createMock(TestProviderInterface::class);
        $providerTwo = $this->createMock(TestProviderInterface::class);

        $this->providers = [$providerOne, $providerTwo];

        $this->controllerNameResolver = $this->createMock(ControllerNameResolverInterface::class);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->accessMap = $this->createMock(AccessMapInterface::class);
    }

    public function testSetLogger()
    {
        $builder = new AccessMapBuilder($this->providers);

        $this->providers[0]->expects($this->once())
            ->method('setLogger')
            ->with($this->logger);

        $this->providers[1]->expects($this->once())
            ->method('setLogger')
            ->with($this->logger);

        $builder->setLogger($this->logger);
    }

    public function testBuildWithoutRouteCollectionException()
    {
        $builder = new AccessMapBuilder($this->providers);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot build access map - route collection is not provided.');

        $builder->build($this->accessMap);
    }

    public function testBuildWithoutTestProvidersException()
    {
        $builder = new AccessMapBuilder();
        $builder->setRouteCollection($this->createRouteCollection());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot build access map - no test providers are set.');

        $builder->build($this->accessMap);
    }

    public function testBuildWithImportedRouteCollection()
    {
        $routeCollection = $this->createRouteCollection();

        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())
            ->method('getRouteCollection')
            ->willReturn($routeCollection);

        $builder = new AccessMapBuilder($this->providers);
        $builder->importRouteCollection($router);

        $builder->build($this->accessMap);
    }

    public function testBuild()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $builder = new AccessMapBuilder($this->providers);
        $builder->setRouteCollection($routeCollection);

        $this->accessMap->expects($this->once())
            ->method('clear');

        $testBagOne = $this->createMock(AbstractTestBagInterface::class);
        $testBagTwo = $this->createMock(AbstractTestBagInterface::class);

        $route = $routeCollection->get('/path1');

        $this->providers[0]->expects($this->once())
            ->method('getTests')
            ->with($route, '/path1', 'class::method')
            ->willReturn($testBagOne);

        $this->providers[1]->expects($this->once())
            ->method('getTests')
            ->with($route, '/path1', 'class::method')
            ->willReturn($testBagTwo);

        $testBagOne->expects($this->once())
            ->method('setProviderClass')
            ->with(get_class($this->providers[0]));

        $testBagTwo->expects($this->once())
            ->method('setProviderClass')
            ->with(get_class($this->providers[1]));

        $this->accessMap->expects($this->once())
            ->method('set')
            ->with('/path1', [$testBagOne, $testBagTwo]);

        $builder->build($this->accessMap);
    }

    public function testBuildWithProviderException()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $builder = new AccessMapBuilder($this->providers);
        $builder->setRouteCollection($routeCollection);

        $testBagOne = $this->createMock(AbstractTestBagInterface::class);

        $this->providers[0]->expects($this->once())
            ->method('getTests')
            ->willReturn($testBagOne);

        $this->providers[1]->expects($this->once())
            ->method('getTests')
            ->willThrowException(new InvalidArgumentException('bla bla'));

        $this->accessMap->expects($this->never())
            ->method('set');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('bla bla');

        $builder->build($this->accessMap);
    }

    public function testBuildWithProviderExceptionCaught()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $builder = new AccessMapBuilder($this->providers, ['catch_exceptions' => true]);
        $builder->setRouteCollection($routeCollection);
        $builder->setLogger($this->logger);

        $testBagOne = $this->createMock(AbstractTestBagInterface::class);

        $this->providers[0]->expects($this->once())
            ->method('getTests')
            ->willReturn($testBagOne);

        $this->providers[1]->expects($this->once())
            ->method('getTests')
            ->willThrowException(new InvalidArgumentException('bla bla'));

        $this->accessMap->expects($this->never())
            ->method('set');

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Exception caught while processing route "/path1": bla bla');

        $builder->build($this->accessMap);
    }

    public function testResolveControllerName()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $builder = new AccessMapBuilder($this->providers);
        $builder->setRouteCollection($routeCollection);

        $this->controllerNameResolver->method('resolve')
            ->with('class::method')
            ->willReturn('class2::method2');

        $builder->setControllerNameResolver($this->controllerNameResolver);

        $route = $routeCollection->get('/path1');

        $this->providers[0]->expects($this->once())
            ->method('getTests')
            ->with($route, '/path1', 'class2::method2');

        $builder->build($this->accessMap);
    }

    public function testIgnoredControllers()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class1::method',
            '/path2' => 'class2::method1',
            '/path3' => 'class2::method2',
        ]);

        $ignoredControllers = [
            'class1',
            'class2::method2',
        ];

        $this->providers[0]->expects($this->once())
            ->method('getTests')
            ->with($routeCollection->get('/path2'));

        $this->accessMap->expects($this->once())
            ->method('set')
            ->with('/path2');

        $builder = new AccessMapBuilder($this->providers, ['ignore_controllers' => $ignoredControllers]);
        $builder->setRouteCollection($routeCollection);
        $builder->setLogger($this->logger);

        $builder->build($this->accessMap);
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
