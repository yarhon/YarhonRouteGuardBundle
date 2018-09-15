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
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\DefaultValueResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class DefaultValueResolverTest extends TestCase
{
    private $context;

    private $argument;

    public function setUp()
    {
        $this->context = $this->createMock(ArgumentResolverContext::class);

        $this->argument = $this->createMock(ArgumentMetadata::class);
    }

    public function testSupportsNotHasDefaultValue()
    {
        $resolver = new DefaultValueResolver();

        $this->argument->method('hasDefaultValue')
            ->willReturn(false);

        $this->assertFalse($resolver->supports($this->context, $this->argument));
    }

    public function testSupportsHasDefaultValue()
    {
        $resolver = new DefaultValueResolver();

        $this->argument->method('hasDefaultValue')
            ->willReturn(true);

        $this->assertTrue($resolver->supports($this->context, $this->argument));
    }

    public function testSupportsNotHasNullableValue()
    {
        $resolver = new DefaultValueResolver();

        $this->argument->method('hasDefaultValue')
            ->willReturn(false);

        $this->argument->method('isNullable')
            ->willReturn(false);

        $this->assertFalse($resolver->supports($this->context, $this->argument));
    }

    public function testSupportsHasNullableValue()
    {
        $resolver = new DefaultValueResolver();

        $this->argument->method('hasDefaultValue')
            ->willReturn(false);

        $this->argument->method('getType')
            ->willReturn('int');

        $this->argument->method('isNullable')
            ->willReturn(true);

        $this->argument->method('isVariadic')
            ->willReturn(false);

        $this->assertTrue($resolver->supports($this->context, $this->argument));
    }

    public function testResolveDefaultValue()
    {
        $resolver = new DefaultValueResolver();

        $this->argument->method('hasDefaultValue')
            ->willReturn(true);

        $this->argument->method('getDefaultValue')
            ->willReturn(5);

        $this->assertEquals(5, $resolver->resolve($this->context, $this->argument));
    }

    public function testResolveNullValue()
    {
        $resolver = new DefaultValueResolver();

        $this->argument->method('hasDefaultValue')
            ->willReturn(false);

        $this->assertNull($resolver->resolve($this->context, $this->argument));
    }
}
