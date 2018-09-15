<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Controller\ArgumentResolver;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\DependencyInjection\Exception\RuntimeException as ContainerRuntimeException;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ServiceValueResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ServiceValueResolverTest extends TestCase
{
    private $container;

    private $context;

    private $argument;

    private $resolver;

    public function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);

        $this->context = $this->createMock(ArgumentResolverContext::class);

        $this->argument = $this->createMock(ArgumentMetadata::class);

        $this->argument->method('getName')
            ->willReturn('arg');

        $this->resolver = new ServiceValueResolver($this->container);
    }

    public function testSupportsNoController()
    {
        $this->context->method('getControllerName')
            ->willReturn(false);

        $this->assertFalse($this->resolver->supports($this->context, $this->argument));
    }

    public function testSupportsNoServiceLocator()
    {
        $this->context->method('getControllerName')
            ->willReturn('a::b');

        $this->container->expects($this->at(0))
            ->method('has')
            ->willReturn(false);

        $this->assertFalse($this->resolver->supports($this->context, $this->argument));
    }

    public function testSupportsTriesOldServiceNaming()
    {
        $this->context->method('getControllerName')
            ->willReturn('a::b');

        $this->container->expects($this->at(0))
            ->method('has')
            ->with('a::b')
            ->willReturn(false);

        $this->container->expects($this->at(1))
            ->method('has')
            ->with('a:b')
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with('a:b');

        $this->resolver->supports($this->context, $this->argument);
    }

    public function testSupportsNoServiceInServiceLocator()
    {
        $this->context->method('getControllerName')
            ->willReturn('a::b');

        $serviceLocator = $this->createMock(ContainerInterface::class);

        $serviceLocator->method('has')
            ->willReturn(false);

        $this->container->expects($this->once())
            ->method('has')
            ->with('a::b')
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with('a::b')
            ->willReturn($serviceLocator);

        $this->assertFalse($this->resolver->supports($this->context, $this->argument));
    }

    public function testSupportsHasServiceInServiceLocator()
    {
        $this->context->method('getControllerName')
            ->willReturn('a::b');

        $serviceLocator = $this->createMock(ContainerInterface::class);

        $serviceLocator->method('has')
            ->with('arg')
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('has')
            ->with('a::b')
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with('a::b')
            ->willReturn($serviceLocator);

        $this->assertTrue($this->resolver->supports($this->context, $this->argument));
    }

    public function testResolve()
    {
        $this->context->method('getControllerName')
            ->willReturn('a::b');

        $serviceLocator = $this->createMock(ContainerInterface::class);

        $value = new \stdClass();

        $serviceLocator->method('get')
            ->with('arg')
            ->willReturn($value);

        $this->container->expects($this->once())
            ->method('has')
            ->with('a::b')
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with('a::b')
            ->willReturn($serviceLocator);

        $this->assertSame($value, $this->resolver->resolve($this->context, $this->argument));
    }

    public function testResolveException()
    {
        $this->context->method('getControllerName')
            ->willReturn('a::b');

        $serviceLocator = $this->createMock(ContainerInterface::class);

        $serviceLocator->method('has')
            ->with('arg')
            ->willReturn(true);

        $exception = new ContainerRuntimeException('original text');

        $serviceLocator->method('get')
            ->with('arg')
            ->willThrowException($exception);

        $this->container->expects($this->once())
            ->method('has')
            ->with('a::b')
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with('a::b')
            ->willReturn($serviceLocator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve argument $arg of "a::b()": original text');

        $this->resolver->resolve($this->context, $this->argument);
    }
}
