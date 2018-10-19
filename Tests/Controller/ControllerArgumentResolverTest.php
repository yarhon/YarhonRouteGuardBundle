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
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContextInterface;
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

        $argumentMetadata =  new ArgumentMetadata('arg1', 'int', false, false, null);

        $controllerMetadata = new ControllerMetadata('class::method', [
            $argumentMetadata,
        ]);

        $requestAttributes = new ParameterBag(['a' => 1]);

        $this->metadataFactory->method('createMetadata')
            ->with($routeContext->getName())
            ->willReturn($controllerMetadata);

        $this->requestAttributesFactory->method('getAttributes')
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

    public function atestGetArgumentException()
    {
        $context = $this->createMock(ArgumentResolverContextInterface::class);
        $metadata = $this->createMock(ArgumentMetadata::class);

        $context->method('getControllerName')
            ->willReturn('a::b');

        $metadata->method('getName')
            ->willReturn('page');

        $argumentResolver = new ControllerArgumentResolver($this->cache, $this->requestAttributesFactory, $this->requestStack);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Controller "a::b" requires that you provide a value for the "$page" argument.');

        $argumentResolver->getArgument($context, $metadata);
    }

}
