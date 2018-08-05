<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Routing\RouteCollection;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Yarhon\LinkGuardBundle\Tests\HelperTrait;
use Yarhon\LinkGuardBundle\Routing\RouteCollection\ControllerNameDeprecationsTransformer;
use Yarhon\LinkGuardBundle\Controller\ControllerNameDeprecationsConverter;
use Yarhon\LinkGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerNameDeprecationsTransformerTest extends TestCase
{
    use HelperTrait;

    /**
     * @var ControllerNameDeprecationsTransformer
     */
    private $transformer;

    /**
     * @var MockObject
     */
    private $converter;

    public function setUp()
    {
        $this->converter = $this->createMock(ControllerNameDeprecationsConverter::class);
        $this->transformer = new ControllerNameDeprecationsTransformer($this->converter);
    }

    public function testTransform()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path2' => 'class::method',
            '/path3' => false,
        ]);

        $this->converter->method('convert')
            ->willReturn('zzz');

        $this->converter->expects($this->exactly(2))
            ->method('convert');

        $transformed = $this->transformer->transform($routeCollection);

        $expected = $this->createRouteCollection([
            '/path1' => 'zzz',
            '/path2' => 'zzz',
            '/path3' => false,
        ]);

        $this->assertEquals($expected, $transformed);
    }

    public function testTransformException()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class',
        ]);

        $this->converter->method('convert')
            ->willThrowException(new InvalidArgumentException('Q'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to convert controller name for route "/path1": Q');

        $this->transformer->transform($routeCollection);
    }
}
