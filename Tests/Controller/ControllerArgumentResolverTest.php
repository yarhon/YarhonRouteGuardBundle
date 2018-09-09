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
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentValueResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContextInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerArgumentResolverTest extends TestCase
{
    public function testGetArgument()
    {
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

        $argumentResolver = new ControllerArgumentResolver([$valueResolverOne, $valueResolverTwo]);

        $context = $this->createMock(ArgumentResolverContextInterface::class);
        $metadata = $this->createMock(ArgumentMetadata::class);

        $value = $argumentResolver->getArgument($context, $metadata);

        $this->assertEquals(5, $value);
    }

    public function testGetArgumentException()
    {
        $context = $this->createMock(ArgumentResolverContextInterface::class);
        $metadata = $this->createMock(ArgumentMetadata::class);

        $context->method('getControllerName')
            ->willReturn('a::b');

        $metadata->method('getName')
            ->willReturn('page');

        $argumentResolver = new ControllerArgumentResolver();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Controller "a::b" requires that you provide a value for the "$page" argument.');

        $argumentResolver->getArgument($context, $metadata);
    }
}
