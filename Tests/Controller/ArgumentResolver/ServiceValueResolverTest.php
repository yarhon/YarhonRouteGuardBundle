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

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports($controllerName, $serviceLocator, $expected)
    {
        $this->context->method('getControllerName')
            ->willReturn($controllerName);

        $this->container->method('has')
            ->willReturn((bool) $serviceLocator);

        $this->container->method('get')
            ->willReturn($serviceLocator);

        $this->assertSame($expected, $this->resolver->supports($this->context, $this->argument));
    }

    public function supportsDataProvider()
    {
        return [
            // no service locator
            [
                'a::b',
                null,
                false,
            ],
            // no service in service locator
            [
                'a::b',
                $this->createServiceLocator(),
                false,
            ],
            // has service in service locator
            [
                'a::b',
                $this->createServiceLocator('arg'),
                true,
            ],
        ];
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

    public function testSupportsTrimsLeadingBackslashes()
    {
        $this->context->method('getControllerName')
            ->willReturn('\\a::b');

        $this->container->expects($this->at(0))
            ->method('has')
            ->with('a::b');

        $this->resolver->supports($this->context, $this->argument);
    }

    public function testResolve()
    {
        $this->context->method('getControllerName')
            ->willReturn('a::b');

        $value = new \stdClass();

        $serviceLocator = $this->createServiceLocator('arg', $value);

        $this->container->method('has')
            ->with('a::b')
            ->willReturn(true);

        $this->container->method('get')
            ->with('a::b')
            ->willReturn($serviceLocator);

        $this->assertSame($value, $this->resolver->resolve($this->context, $this->argument));
    }

    public function testResolveException()
    {
        $this->context->method('getControllerName')
            ->willReturn('a::b');

        $serviceLocator = $this->createServiceLocator('arg');

        $exception = new ContainerRuntimeException('original text');

        $serviceLocator->method('get')
            ->with('arg')
            ->willThrowException($exception);

        $this->container->method('has')
            ->with('a::b')
            ->willReturn(true);

        $this->container->method('get')
            ->with('a::b')
            ->willReturn($serviceLocator);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve argument $arg of "a::b()": original text');

        $this->resolver->resolve($this->context, $this->argument);
    }

    private function createServiceLocator($serviceId = null, $serviceValue = null)
    {
        $serviceLocator = $this->createMock(ContainerInterface::class);

        if (null !== $serviceId) {
            $serviceLocator->method('has')
                ->with($serviceId)
                ->willReturn(true);
        } else {
            $serviceLocator->method('has')
                ->willReturn(false);
        }

        if (null !== $serviceValue) {
            $serviceLocator->method('get')
                ->with($serviceId)
                ->willReturn($serviceValue);
        }

        return $serviceLocator;
    }
}
