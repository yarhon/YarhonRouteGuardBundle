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
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadataFactory;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

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

        $this->factory = new ControllerMetadataFactory($this->controllerNameResolver, $this->argumentMetadataFactory);
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
        $this->assertEquals($argumentMetadatas, $metadata->all());
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
