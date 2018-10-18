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
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentValueResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContextInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerArgumentResolverTest extends TestCase
{
    private $cache;

    private $requestAttributesFactory;

    private $requestStack;

    public function setUp()
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);

        $this->requestAttributesFactory = $this->createMock(RequestAttributesFactory::class);

        $this->requestStack = $this->createMock(RequestStack::class);

        $request = $this->createMock(Request::class);

        $this->requestStack->method('getCurrentRequest')
            ->willReturn($request);
    }

    public function atestGetArgument()
    {
        //$requestAttributes = $this->createMock(ParameterBag::class);


        $valueResolverOne = $this->createMock(ArgumentValueResolverInterface::class);
        $valueResolverTwo = $this->createMock(ArgumentValueResolverInterface::class);

        $context = $this->createMock(ArgumentResolverContextInterface::class);
        $metadata = $this->createMock(ArgumentMetadata::class);

        $valueResolverOne->method('supports')
            ->willReturn(false);

        $valueResolverOne->expects($this->never())
            ->method('resolve');

        $valueResolverTwo->method('supports')
            ->willReturn(true);

        $valueResolverTwo->method('resolve')
            ->willReturn(5);

        $valueResolverTwo->expects($this->once())
            ->method('resolve')
            ->with($context, $metadata);

        $argumentResolver = new ControllerArgumentResolver($this->cache, $this->requestAttributesFactory, $this->requestStack, [$valueResolverOne, $valueResolverTwo]);

        $context = $this->createMock(ArgumentResolverContextInterface::class);
        $metadata = $this->createMock(ArgumentMetadata::class);

        $value = $argumentResolver->getArgument($context, $metadata);

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
