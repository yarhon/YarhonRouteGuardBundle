<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Routing\RouteCollection;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Yarhon\RouteGuardBundle\Tests\HelperTrait;
use Yarhon\RouteGuardBundle\Routing\RouteCollection\ControllerNameTransformer;
use Yarhon\RouteGuardBundle\Controller\ContainerControllerNameResolver;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerNameTransformerTest extends TestCase
{
    use HelperTrait;

    /**
     * @var ControllerNameTransformer
     */
    private $transformer;

    /**
     * @var MockObject
     */
    private $resolver;

    public function setUp()
    {
        $this->resolver = $this->createMock(ContainerControllerNameResolver::class);
        $this->transformer = new ControllerNameTransformer($this->resolver);
    }

    public function testTransform()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $this->resolver->method('resolve')
            ->willReturn('zzz');

        $this->resolver->expects($this->exactly(1))
            ->method('resolve');

        $transformed = $this->transformer->transform($routeCollection);

        $expected = $this->createRouteCollection([
            '/path1' => 'zzz',
        ]);

        $this->assertEquals($expected, $transformed);
    }

    public function testTransformException()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class',
        ]);

        $this->resolver->method('resolve')
            ->willThrowException(new InvalidArgumentException('Q'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to resolve controller name for route "/path1": Q');

        $this->transformer->transform($routeCollection);
    }
}
