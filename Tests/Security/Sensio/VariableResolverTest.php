<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Sensio;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Security\Sensio\VariableResolver;
use Yarhon\RouteGuardBundle\Security\Sensio\VariableResolverContext;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class VariableResolverTest extends TestCase
{
    private $requestAttributesFactory;

    private $controllerArgumentResolver;

    private $requestStack;

    private $routeMetadata;

    private $controllerMetadata;

    private $requestAttributes;

    private $variableResolver;

    public function setUp()
    {
        $this->requestAttributesFactory = $this->createMock(RequestAttributesFactory::class);
        $this->controllerArgumentResolver = $this->createMock(ControllerArgumentResolver::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->variableResolver = new VariableResolver($this->requestAttributesFactory, $this->controllerArgumentResolver, $this->requestStack);

        $this->routeMetadata = $this->createMock(RouteMetadataInterface::class);
        $this->controllerMetadata = $this->createMock(ControllerMetadata::class);
        $this->requestAttributes = $this->createMock(ParameterBag::class);
    }

    public function testCreateContext()
    {
        $parameters = ['foo' => 1];

        $this->requestAttributesFactory->expects($this->once())
            ->method('getAttributes')
            ->with($this->routeMetadata, $parameters)
            ->willReturn($this->requestAttributes);

        $context = $this->variableResolver->createContext($this->routeMetadata, $this->controllerMetadata, $parameters);

        $this->assertSame($this->routeMetadata, $context->getRouteMetadata());
        $this->assertSame($this->controllerMetadata, $context->getControllerMetadata());
        $this->assertSame($this->requestAttributes, $context->getRequestAttributes());
    }

    public function testGetVariableFromControllerArguments()
    {
        $variableResolverContext = $this->createConfiguredMock(
            VariableResolverContext::class,
            [
                'getRouteMetadata' => $this->routeMetadata,
                'getControllerMetadata' => $this->controllerMetadata,
                'getRequestAttributes' => $this->requestAttributes,
            ]
        );

        $this->controllerMetadata->method('has')
            ->with('foo')
            ->willReturn(true);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);

        $this->controllerMetadata->method('get')
            ->with('foo')
            ->willReturn($argumentMetadata);

        $argumentResolverContext = $this->createMock(ArgumentResolverContext::class);

        $this->controllerArgumentResolver->method('createContext')
            ->willReturn($argumentResolverContext);

        $this->controllerArgumentResolver->expects($this->once())
            ->method('getArgument')
            ->with($argumentResolverContext, $argumentMetadata)
            ->willReturn(5);

        $value = $this->variableResolver->getVariable($variableResolverContext, 'foo');

        $this->assertEquals(5, $value);
    }

    public function testGetVariableFromRequestAttributes()
    {
        $variableResolverContext = $this->createConfiguredMock(
            VariableResolverContext::class,
            [
                'getRouteMetadata' => $this->routeMetadata,
                'getControllerMetadata' => $this->controllerMetadata,
                'getRequestAttributes' => $this->requestAttributes,
            ]
        );

        $this->controllerMetadata->method('has')
            ->with('foo')
            ->willReturn(false);

        $this->requestAttributes->method('has')
            ->with('foo')
            ->willReturn(true);

        $this->requestAttributes->method('get')
            ->with('foo')
            ->willReturn(5);

        $value = $this->variableResolver->getVariable($variableResolverContext, 'foo');

        $this->assertEquals(5, $value);
    }

    public function testGetVariableException()
    {
        $variableResolverContext = $this->createConfiguredMock(
            VariableResolverContext::class,
            [
                'getRouteMetadata' => $this->routeMetadata,
                'getControllerMetadata' => $this->controllerMetadata,
                'getRequestAttributes' => $this->requestAttributes,
            ]
        );

        $this->controllerMetadata->method('has')
            ->with('foo')
            ->willReturn(false);

        $this->requestAttributes->method('has')
            ->with('foo')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Variable is neither a controller argument nor request attribute.');

        $this->variableResolver->getVariable($variableResolverContext, 'foo');
    }

    public function testGetVariableNames()
    {
        $this->controllerMetadata->method('keys')
            ->willReturn(['arg_foo', 'arg_bar']);

        $this->controllerMetadata->method('has')
            ->willReturn(false);

        $this->requestAttributes->method('keys')
            ->willReturn(['attr_foo', 'attr_bar']);

        $this->requestAttributesFactory->expects($this->once())
            ->method('getAttributesPrototype')
            ->with($this->routeMetadata)
            ->willReturn($this->requestAttributes);

        $names = $this->variableResolver->getVariableNames($this->routeMetadata, $this->controllerMetadata);

        $this->assertEquals(['arg_foo', 'arg_bar', 'attr_foo', 'attr_bar'], $names);
    }
}
