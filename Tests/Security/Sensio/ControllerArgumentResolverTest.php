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
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactoryInterface;
use Yarhon\RouteGuardBundle\Security\Sensio\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerArgumentResolverTest extends TestCase
{
    private $delegate;

    private $requestAttributesFactory;

    private $resolver;

    public function setUp()
    {
        $this->delegate = $this->createMock(ControllerArgumentResolverInterface::class);

        $this->requestAttributesFactory = $this->createMock(RequestAttributesFactoryInterface::class);

        $this->resolver = new ControllerArgumentResolver($this->delegate, $this->requestAttributesFactory);
    }

    public function testGetArgument()
    {
        $routeContext = new RouteContext('index');

        $this->delegate->method('getArgumentNames')
            ->with('index')
            ->willReturn(['arg1']);

        $this->delegate->method('getArgument')
            ->with($routeContext, 'arg1')
            ->willReturn(5);

        $resolved = $this->resolver->getArgument($routeContext, 'arg1');

        $this->assertEquals(5, $resolved);
    }

    public function testGetArgumentFromRequestAttributes()
    {
        $routeContext = new RouteContext('index');

        $this->delegate->method('getArgumentNames')
            ->with('index')
            ->willReturn([]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->with('index')
            ->willReturn(['arg1']);

        $attributes = new ParameterBag(['arg1' => 5]);

        $this->requestAttributesFactory->method('createAttributes')
            ->with($routeContext)
            ->willReturn($attributes);

        $resolved = $this->resolver->getArgument($routeContext, 'arg1');

        $this->assertEquals(5, $resolved);
    }

    public function testGetArgumentException()
    {
        $routeContext = new RouteContext('index');

        $this->delegate->method('getArgumentNames')
            ->with('index')
            ->willReturn([]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->with('index')
            ->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route "index" argument "arg1" is neither a controller argument nor request attribute.');

        $this->resolver->getArgument($routeContext, 'arg1');
    }

    public function testGetArgumentNames()
    {
        $this->delegate->method('getArgumentNames')
            ->with('index')
            ->willReturn(['page', 'arg1']);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->with('index')
            ->willReturn(['page', 'attr1']);

        $expected = ['page', 'arg1', 'attr1'];

        $this->assertEquals($expected, $this->resolver->getArgumentNames('index'));
    }
}
