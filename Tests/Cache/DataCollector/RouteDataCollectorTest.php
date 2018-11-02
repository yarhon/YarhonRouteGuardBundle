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
use Symfony\Component\Routing\Route;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadataFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;
use Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderAggregate;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Cache\DataCollector\RouteDataCollector;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteDataCollectorTest extends TestCase
{
    private $testProvider;

    private $controllerMetadataFactory;

    private $routeMetadataFactory;

    private $routeDataCollector;

    public function setUp()
    {
        $this->testProvider = $this->createMock(TestProviderAggregate::class);
        $this->controllerMetadataFactory = $this->createMock(ControllerMetadataFactory::class);
        $this->routeMetadataFactory = $this->createMock(RouteMetadataFactory::class);

        $this->routeDataCollector = new RouteDataCollector($this->testProvider, $this->controllerMetadataFactory, $this->routeMetadataFactory);
    }

    public function testCollect()
    {
        $controllerMetadata = new ControllerMetadata('class::method', 'class', 'method');
        $routeMetadata = new RouteMetadata([], []);
        $testBags = [$this->createTestBag()];

        $this->controllerMetadataFactory->method('createMetadata')
            ->willReturn($controllerMetadata);

        $this->routeMetadataFactory->method('createMetadata')
            ->willReturn($routeMetadata);

        $this->testProvider->method('getTests')
            ->willReturn($testBags);

        $collected = $this->routeDataCollector->collect('index', new Route('/'), 'class::method');

        $expected = [$testBags, $controllerMetadata, $routeMetadata];

        $this->assertSame($expected, $collected);
    }

    public function testCollectNoControllerName()
    {
        $routeMetadata = new RouteMetadata([], []);
        $testBags = [$this->createTestBag()];

        $this->routeMetadataFactory->method('createMetadata')
            ->willReturn($routeMetadata);

        $this->testProvider->method('getTests')
            ->willReturn($testBags);

        $collected = $this->routeDataCollector->collect('index', new Route('/'), null);

        $expected = [$testBags, null, $routeMetadata];

        $this->assertSame($expected, $collected);
    }

    private function createTestBag()
    {
        return $this->createMock(TestBagInterface::class);
    }
}
