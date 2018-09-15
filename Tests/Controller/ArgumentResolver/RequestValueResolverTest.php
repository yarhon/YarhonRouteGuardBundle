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
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\Request;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\RequestValueResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestValueResolverTest extends TestCase
{
    private $context;

    private $argument;

    public function setUp()
    {
        $this->context = $this->createMock(ArgumentResolverContext::class);

        $this->argument = $this->createMock(ArgumentMetadata::class);
    }

    public function testSupportsNoRequest()
    {
        $resolver = new RequestValueResolver();

        $this->argument->method('getType')
            ->willReturn('int');

        $this->assertFalse($resolver->supports($this->context, $this->argument));
    }

    public function testSupportsWithRequest()
    {
        $resolver = new RequestValueResolver();

        $this->argument->method('getType')
            ->willReturn(Request::class);

        $this->assertTrue($resolver->supports($this->context, $this->argument));
    }

    public function testSupportsWithRequestChild()
    {
        $resolver = new RequestValueResolver();

        $this->argument->method('getType')
            ->willReturn(RequestChild::class);

        $this->assertTrue($resolver->supports($this->context, $this->argument));
    }

    public function testResolve()
    {
        $resolver = new RequestValueResolver();
        $request = $this->createMock(Request::class);

        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->assertSame($request, $resolver->resolve($this->context, $this->argument));
    }
}

class RequestChild extends Request
{
}
