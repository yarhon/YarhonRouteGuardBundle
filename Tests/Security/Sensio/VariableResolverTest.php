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

    private $requestAttributes;

    private $variableResolver;

    public function setUp()
    {
        $this->requestAttributesFactory = $this->createMock(RequestAttributesFactory::class);
        $this->controllerArgumentResolver = $this->createMock(ControllerArgumentResolver::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->requestAttributes = $this->createMock(ParameterBag::class);

        $this->variableResolver = new VariableResolver($this->requestAttributesFactory, $this->controllerArgumentResolver, $this->requestStack);
    }

    public function testCreateContext()
    {
        $routeMetadata = $this->createMock(RouteMetadataInterface::class);
        $controllerMetadata = $this->createMock(ControllerMetadata::class);
        $parameters = ['foo' => 1];

        $this->requestAttributesFactory->expects($this->once())
            ->method('getAttributes')
            ->with($routeMetadata, $parameters)
            ->willReturn($this->requestAttributes);

        $context = $this->variableResolver->createContext($routeMetadata, $controllerMetadata, $parameters);

        $this->assertSame($routeMetadata, $context->getRouteMetadata());
        $this->assertSame($controllerMetadata, $context->getControllerMetadata());
        $this->assertSame($this->requestAttributes, $context->getRequestAttributes());
    }

    public function testGetVariableFromControllerArguments()
    {
        $routeMetadata = $this->createMock(RouteMetadataInterface::class);
        $controllerMetadata = $this->createMock(ControllerMetadata::class);

        $variableResolverContext = $this->createMock(VariableResolverContext::class);
        $variableResolverContext->method('getRouteMetadata')
            ->willReturn($routeMetadata);
        $variableResolverContext->method('getControllerMetadata')
            ->willReturn($controllerMetadata);
        $variableResolverContext->method('getRequestAttributes')
            ->willReturn($this->requestAttributes);


        $controllerMetadata->method('has')
            ->with('foo')
            ->willReturn(true);

        $argumentMetadata = $this->createMock(ArgumentMetadata::class);

        $controllerMetadata->method('get')
            ->with('foo')
            ->willReturn($argumentMetadata);

        $argumentResolverContext = new ArgumentResolverContext(
            $variableResolverContext->getRequestAttributes(),
            $variableResolverContext->getRouteMetadata()->getControllerName(),
            $this->requestStack->getCurrentRequest()
        );

        $this->controllerArgumentResolver->expects($this->once())
            ->method('getArgument')
            ->with($argumentResolverContext, $argumentMetadata)
            ->willReturn(5);

        $value = $this->variableResolver->getVariable($variableResolverContext, 'foo');

        $this->assertEquals(5, $value);
    }
}
