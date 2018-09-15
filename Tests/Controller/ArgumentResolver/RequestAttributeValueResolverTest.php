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
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestAttributeValueResolverTest extends TestCase
{
    private $attributes;

    private $context;

    private $argument;

    public function setUp()
    {
        $this->attributes = $this->createMock(ParameterBag::class);

        $this->context = $this->createMock(ArgumentResolverContext::class);

        $this->context->method('getAttributes')
            ->willReturn($this->attributes);

        $this->argument = $this->createMock(ArgumentMetadata::class);

        $this->argument->method('isVariadic')
            ->willReturn(false);
    }

    public function testSupportsHasAttribute()
    {
        $resolver = new RequestAttributeValueResolver();

        $this->argument->method('getName')
            ->willReturn('arg');

        $this->attributes->expects($this->once())
            ->method('has')
            ->with('arg')
            ->willReturn(true);

        $this->assertTrue($resolver->supports($this->context, $this->argument));
    }

    public function testSupportsNotHasAttribute()
    {
        $resolver = new RequestAttributeValueResolver();

        $this->argument->method('getName')
            ->willReturn('arg');

        $this->attributes->expects($this->once())
            ->method('has')
            ->with('arg')
            ->willReturn(false);

        $this->assertFalse($resolver->supports($this->context, $this->argument));
    }

    public function testResolve()
    {
        $resolver = new RequestAttributeValueResolver();

        $this->argument->method('getName')
            ->willReturn('arg');

        $this->attributes->expects($this->once())
            ->method('get')
            ->with('arg')
            ->willReturn(5);

        $this->assertEquals(5, $resolver->resolve($this->context, $this->argument));
    }
}
