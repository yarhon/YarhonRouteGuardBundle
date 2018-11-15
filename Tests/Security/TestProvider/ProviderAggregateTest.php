<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\TestProvider;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Route;
use Yarhon\RouteGuardBundle\Security\TestProvider\ProviderAggregate;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\ProviderAwareInterface;
use Yarhon\RouteGuardBundle\Security\TestProvider\ProviderInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ProviderAggregateTest extends TestCase
{
    private $providers;

    private $logger;

    public function setUp()
    {
        $this->providers = [
            $this->createMock(ProviderInterface::class),
            $this->createMock(ProviderInterface::class),
        ];

        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testSetLogger()
    {
        $providerAggregate = new ProviderAggregate($this->providers);

        $this->providers[0]->expects($this->once())
            ->method('setLogger')
            ->with($this->logger);

        $this->providers[1]->expects($this->once())
            ->method('setLogger')
            ->with($this->logger);

        $providerAggregate->setLogger($this->logger);
    }

    public function testGetTests()
    {
        $providerAggregate = new ProviderAggregate($this->providers);

        $route = new Route('/');
        $routeName = 'index';
        $controllerMetadata = new ControllerMetadata('class::method', 'class', 'method');

        $testBags = [
            $this->createMock(AbstractTestBagInterface::class),
            $this->createMock(AbstractTestBagInterface::class),
        ];

        $this->providers[0]->expects($this->once())
            ->method('getTests')
            ->with($routeName, $route, $controllerMetadata)
            ->willReturn($testBags[0]);

        $this->providers[1]->expects($this->once())
            ->method('getTests')
            ->with($routeName, $route, $controllerMetadata)
            ->willReturn($testBags[1]);

        $tests = $providerAggregate->getTests($routeName, $route, $controllerMetadata);

        $this->assertSame($testBags, $tests);
    }

    public function testGetTestsSetsProviderClass()
    {
        $providerAggregate = new ProviderAggregate($this->providers);

        $route = new Route('/');
        $routeName = 'index';
        $controllerMetadata = new ControllerMetadata('class::method', 'class', 'method');

        $testBags = [
            $this->createMock([AbstractTestBagInterface::class, ProviderAwareInterface::class]),
            $this->createMock([AbstractTestBagInterface::class, ProviderAwareInterface::class]),
        ];

        $this->providers[0]->method('getTests')
            ->willReturn($testBags[0]);

        $this->providers[1]->method('getTests')
            ->willReturn($testBags[1]);

        $testBags[0]->expects($this->once())
            ->method('setProviderClass')
            ->with(get_class($this->providers[0]));

        $testBags[1]->expects($this->once())
            ->method('setProviderClass')
            ->with(get_class($this->providers[1]));

        $providerAggregate->getTests($routeName, $route, $controllerMetadata);
    }
}
