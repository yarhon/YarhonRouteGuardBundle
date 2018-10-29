<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Routing\Route;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ClassMap;
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadataFactory;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerMetadataFactoryTest extends TestCase
{
    private $controllerNameResolver;

    private $argumentMetadataFactory;

    private $factory;

    public function setUp()
    {
        $this->controllerNameResolver = $this->createMock(ControllerNameResolverInterface::class);
        $this->argumentMetadataFactory = $this->createMock(ArgumentMetadataFactoryInterface::class);

        $classMap = [
            'service1' => 'service1_class',
            'service2' => null,
        ];

        $classMap = new ClassMap($classMap);

        $this->factory = new ControllerMetadataFactory($this->controllerNameResolver, $this->argumentMetadataFactory, $classMap);
    }

    public function testCreateMetadata()
    {
        $route = new Route('/', ['_controller' => 'zxc']);

        $this->controllerNameResolver->method('resolve')
            ->with('zxc')
            ->willReturn('class::method');

        $argumentMetadatas = [
            new ArgumentMetadata('arg1', 'int', false, false, null),
            new ArgumentMetadata('arg2', 'string', false, false, null),
        ];

        $this->argumentMetadataFactory->method('createArgumentMetadata')
            ->with(['class', 'method'])
            ->willReturn($argumentMetadatas);

        $metadata = $this->factory->createMetadata($route);

        $this->assertInstanceOf(ControllerMetadata::class, $metadata);
        $this->assertEquals('class::method', $metadata->getName());
        $this->assertEquals('class', $metadata->getClass());
        $this->assertEquals('method', $metadata->getMethod());
        $this->assertEquals(array_combine(['arg1', 'arg2'], $argumentMetadatas), $metadata->getArguments());
        $this->assertNull($metadata->getServiceId());
    }

    public function testCreateMetadataForControllerAsService()
    {
        $route = new Route('/', ['_controller' => 'zxc']);

        $this->controllerNameResolver->method('resolve')
            ->with('zxc')
            ->willReturn('service1::method');

        $this->argumentMetadataFactory->method('createArgumentMetadata')
            ->willReturn([]);

        $metadata = $this->factory->createMetadata($route);

        $this->assertInstanceOf(ControllerMetadata::class, $metadata);
        $this->assertEquals('service1::method', $metadata->getName());
        $this->assertEquals('service1_class', $metadata->getClass());
        $this->assertEquals('method', $metadata->getMethod());
        $this->assertEquals('service1', $metadata->getServiceId());
    }

    public function testCreateMetadataForControllerAsServiceException()
    {
        $route = new Route('/', ['_controller' => 'zxc']);

        $this->controllerNameResolver->method('resolve')
            ->with('zxc')
            ->willReturn('service2::method');

        $this->argumentMetadataFactory->method('createArgumentMetadata')
            ->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to resolve class for service "service2".');

        $this->factory->createMetadata($route);
    }

    public function testCreateMetadataNoControllerName()
    {
        $route = new Route('/', ['_controller' => 'zxc']);

        $this->controllerNameResolver->method('resolve')
            ->with('zxc')
            ->willReturn(null);

        $metadata = $this->factory->createMetadata($route);

        $this->assertNull($metadata);
    }
}
