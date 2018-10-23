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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadataFactory;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentValueResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerArgumentResolverTest extends TestCase
{
    private $metadataFactory;

    private $requestAttributesFactory;

    private $request;

    private $valueResolvers;

    private $resolver;

    public function setUp()
    {
        $this->metadataFactory = $this->createMock(ControllerMetadataFactory::class);

        $this->requestAttributesFactory = $this->createMock(RequestAttributesFactory::class);

        $this->request = $this->createMock(Request::class);

        $requestStack = $this->createMock(RequestStack::class);

        $requestStack->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->valueResolvers = [
            $this->createMock(ArgumentValueResolverInterface::class),
            $this->createMock(ArgumentValueResolverInterface::class),
        ];

        $this->resolver = new ControllerArgumentResolver($this->metadataFactory, $this->requestAttributesFactory, $requestStack, $this->valueResolvers);
    }

    public function testGetArgument()
    {
        $routeContext = new RouteContext('index');

        $argumentMetadata = new ArgumentMetadata('arg1', 'int', false, false, null);

        $controllerMetadata = new ControllerMetadata('class::method', [$argumentMetadata]);

        $requestAttributes = new ParameterBag(['a' => 1]);

        $this->metadataFactory->method('createMetadata')
            ->with($routeContext->getName())
            ->willReturn($controllerMetadata);

        $this->requestAttributesFactory->method('createAttributes')
            ->with($routeContext)
            ->willReturn($requestAttributes);

        $resolverContext = new ArgumentResolverContext($requestAttributes, $controllerMetadata->getName(), $this->request);

        $this->valueResolvers[0]->method('supports')
            ->willReturn(false);

        $this->valueResolvers[0]->expects($this->never())
            ->method('resolve');

        $this->valueResolvers[1]->method('supports')
            ->willReturn(true);

        $this->valueResolvers[1]->expects($this->once())
            ->method('resolve')
            ->with($resolverContext, $argumentMetadata)
            ->willReturn(5);

        $value = $this->resolver->getArgument($routeContext, 'arg1');

        $this->assertEquals(5, $value);
    }

    public function testGetArgumentNoControllerException()
    {
        $routeContext = new RouteContext('index');

        $this->metadataFactory->method('createMetadata')
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route "index" does not have controller or controller name is unresolvable.');

        $this->resolver->getArgument($routeContext, 'arg1');
    }

    public function testGetArgumentNotExistingArgumentException()
    {
        $routeContext = new RouteContext('index');

        $this->metadataFactory->method('createMetadata')
            ->willReturn(new ControllerMetadata('class::method', []));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route "index" controller "class::method" does not have argument "$arg1".');

        $this->resolver->getArgument($routeContext, 'arg1');
    }

    public function testGetArgumentNotResolvableArgumentException()
    {
        $routeContext = new RouteContext('index');

        $argumentMetadata = new ArgumentMetadata('arg1', 'int', false, false, null);
        $controllerMetadata = new ControllerMetadata('class::method', [$argumentMetadata]);

        $this->metadataFactory->method('createMetadata')
            ->willReturn($controllerMetadata);

        $this->requestAttributesFactory->method('createAttributes')
            ->willReturn(new ParameterBag());

        $this->valueResolvers[0]->method('supports')
            ->willReturn(false);

        $this->valueResolvers[1]->method('supports')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route "index" controller "class::method" requires that you provide a value for the "$arg1" argument.');

        $this->resolver->getArgument($routeContext, 'arg1');
    }

    public function testGetArgumentCache()
    {
        $routeContext = new RouteContext('index');

        $controllerMetadata = new ControllerMetadata('class::method', [
            new ArgumentMetadata('arg1', 'int', false, false, null),
            new ArgumentMetadata('arg2', 'int', false, false, null),
        ]);

        $this->metadataFactory->method('createMetadata')
            ->willReturn($controllerMetadata);

        $this->requestAttributesFactory->method('createAttributes')
            ->willReturn(new ParameterBag());

        $this->valueResolvers[0]->method('supports')
            ->willReturn(true);

        $resolvedValueOne = new \stdClass();
        $resolvedValueTwo = new \stdClass();

        $this->valueResolvers[0]->method('resolve')
            ->willReturnOnConsecutiveCalls($resolvedValueOne, $resolvedValueTwo);

        $this->valueResolvers[0]->expects($this->exactly(2))
            ->method('supports');

        $this->valueResolvers[0]->expects($this->exactly(2))
            ->method('resolve');

        $resolvedOne = $this->resolver->getArgument($routeContext, 'arg1');
        $resolvedTwo = $this->resolver->getArgument($routeContext, 'arg2');
        $resolvedThree = $this->resolver->getArgument($routeContext, 'arg1');
        $resolvedFour = $this->resolver->getArgument($routeContext, 'arg2');

        $this->assertSame($resolvedOne, $resolvedThree);
        $this->assertSame($resolvedTwo, $resolvedFour);
        $this->assertNotSame($resolvedOne, $resolvedTwo);
    }

    public function testGetArgumentNames()
    {
        $controllerMetadata = new ControllerMetadata('class::method', [
            new ArgumentMetadata('arg1', 'int', false, false, null),
            new ArgumentMetadata('arg2', 'int', false, false, null),
        ]);

        $this->metadataFactory->method('createMetadata')
            ->with('index')
            ->willReturn($controllerMetadata);

        $names = $this->resolver->getArgumentNames('index');

        $this->assertEquals(['arg1', 'arg2'], $names);
    }

    public function testGetArgumentNamesNoController()
    {
        $this->metadataFactory->method('createMetadata')
            ->with('index')
            ->willReturn(null);

        $names = $this->resolver->getArgumentNames('index');

        $this->assertEquals([], $names);
    }
}
