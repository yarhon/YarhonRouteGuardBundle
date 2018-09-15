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
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolver;
use Yarhon\RouteGuardBundle\Controller\ControllerNameConverter;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;
use Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\SimpleController;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerNameResolverTest extends TestCase
{
    /**
     * @var ControllerNameResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->resolver = new ControllerNameResolver();
    }

    /**
     * @dataProvider resolveProvider
     */
    public function testResolve($controller, $expected)
    {
        $resolved = $this->resolver->resolve($controller);

        $this->assertEquals($expected, $resolved);
    }

    public function resolveProvider()
    {
        return [
            [
                [SimpleController::class, 'index'],
                'Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\SimpleController::index',
            ],
            [
                [new SimpleController(), 'index'],
                'Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\SimpleController::index',
            ],
            [
                new SimpleController(),
                'Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\SimpleController::__invoke',
            ],
            [
                'array_map',
                false,
            ],
            [
                'Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\SimpleController',
                'Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\SimpleController::__invoke',
            ],
            [
                'Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\SimpleController::index',
                'Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\SimpleController::index',
            ],
        ];
    }

    public function testResolveException()
    {
        $this->expectException(InvalidArgumentException::class);

        $resolved = $this->resolver->resolve([]);
    }

    public function testConverterCall()
    {
        $converter = $this->createMock(ControllerNameConverter::class);

        $this->resolver->setConverter($converter);

        $converter->expects($this->once())
            ->method('convert')
            ->with('a::b');

        $this->resolver->resolve('a::b');
    }
}
