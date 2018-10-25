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
use Symfony\Component\Routing\Route;
use Yarhon\RouteGuardBundle\Security\RouteTestCollector;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteTestCollectorTest extends TestCase
{
    private $providers;

    private $logger;

    public function setUp()
    {
        $this->providers = [
            $this->createMock(TestProviderInterface::class),
            $this->createMock(TestProviderInterface::class),
        ];

        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testSetLogger()
    {
        $collector = new RouteTestCollector($this->providers);

        $this->providers[0]->expects($this->once())
            ->method('setLogger')
            ->with($this->logger);

        $this->providers[1]->expects($this->once())
            ->method('setLogger')
            ->with($this->logger);

        $collector->setLogger($this->logger);
    }

    public function testBuildWithoutTestProvidersException()
    {
        $collector = new RouteTestCollector();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Test providers collection is empty.');

        $collector->getTests('index', new Route('/'));
    }

    public function testGetTests()
    {
        $collector = new RouteTestCollector($this->providers);

        $route = new Route('/');
        $routeName = 'index';
        $controllerMetadata = new ControllerMetadata('class::method');

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

        $testBags[0]->expects($this->once())
            ->method('setProviderClass')
            ->with(get_class($this->providers[0]));

        $testBags[1]->expects($this->once())
            ->method('setProviderClass')
            ->with(get_class($this->providers[1]));

        $tests = $collector->getTests($routeName, $route, $controllerMetadata);

        $this->assertSame($testBags, $tests);
    }
}