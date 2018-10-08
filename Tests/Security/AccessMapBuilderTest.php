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
    private $providerOne;

    private $providerTwo;

    private $providers;

    private $controllerNameResolver;

    private $logger;

    private $accessMap;

    public function setUp()
    {
        $this->providerOne = $this->createMock(TestProviderInterface::class);
        $this->providerTwo = $this->createMock(TestProviderInterface::class);

        $this->providers = [$this->providerOne, $this->providerTwo];

        $this->controllerNameResolver = $this->createMock(ControllerNameResolverInterface::class);

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->accessMap = $this->createMock(AccessMapInterface::class);
    }

    public function testSetLogger()
    {
        $builder = new AccessMapBuilder($this->providers);

        $this->providerOne->expects($this->once())
            ->method('setLogger')
            ->with($this->logger);

        $this->providerTwo->expects($this->once())
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

        $this->providerOne->expects($this->once())
            ->method('getTests')
            ->with($route, 'class::method')
            ->willReturn($testBagOne);

        $this->providerTwo->expects($this->once())
            ->method('getTests')
            ->with($route, 'class::method')
            ->willReturn($testBagTwo);

        $testBagOne->expects($this->once())
            ->method('setProviderClass')
            ->with(get_class($this->providerOne));

        $testBagTwo->expects($this->once())
            ->method('setProviderClass')
            ->with(get_class($this->providerTwo));

        $this->accessMap->expects($this->once())
            ->method('add')
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

        $this->providerOne->expects($this->once())
            ->method('getTests')
            ->willReturn($testBagOne);

        $exception = new InvalidArgumentException('bla bla');

        $this->providerTwo->expects($this->once())
            ->method('getTests')
            ->willThrowException($exception);

        $this->accessMap->expects($this->never())
            ->method('add');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('bla bla');

        $builder->build($this->accessMap);
    }

    public function testBuildWithProviderExceptionCaught()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $builder = new AccessMapBuilder($this->providers, ['throw_exceptions' => false]);
        $builder->setRouteCollection($routeCollection);

        $testBagOne = $this->createMock(AbstractTestBagInterface::class);

        $this->providerOne->expects($this->once())
            ->method('getTests')
            ->willReturn($testBagOne);

        $exception = new InvalidArgumentException('bla bla');

        $this->providerTwo->expects($this->once())
            ->method('getTests')
            ->willThrowException($exception);

        $this->accessMap->expects($this->never())
            ->method('add');

        $this->accessMap->expects($this->once())
            ->method('addException')
            ->with('/path1', $exception);


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

        $this->providerOne->expects($this->once())
            ->method('getTests')
            ->with($route, 'class2::method2');

        $builder->build($this->accessMap);
    }


    public function atestIgnoredControllers()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path2' => 'class2::method',
            '/path3' => 'extra_class::method',
            '/path4' => false,
        ]);

        $ignoredControllers = [
            'class2',
            'extra',
        ];

        $transformer = new RemoveIgnoredTransformer($ignoredControllers);
        $transformed = $transformer->transform($routeCollection);

        $expected = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path4' => false,
        ]);

        $this->assertEquals($expected, $transformed);
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

    public function aTestClear()
    {
        $accessMap = $this->createMock(AccessMap::class);

        $accessMap->expects($this->once())
            ->method('clear');
    }
}
