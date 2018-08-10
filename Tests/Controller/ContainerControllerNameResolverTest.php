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
use Yarhon\RouteGuardBundle\Controller\ContainerControllerNameResolver;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ClassMap;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ContainerControllerNameResolverTest extends TestCase
{
    /**
     * @var ContainerControllerNameResolver
     */
    private $resolver;

    public function setUp()
    {
        $classMap = [
            'service1' => 'service1_class',
            'service2' => null,
        ];

        $classMap = new ClassMap($classMap);
        $this->resolver = new ContainerControllerNameResolver($classMap);
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
                ['other_class', 'index'],
                'other_class::index',
            ],
            [
                ['service1', 'index'],
                'service1_class::index',
            ],
            [
                'service1',
                'service1_class::__invoke',
            ],
            [
                'service1::index',
                'service1_class::index',
            ],
        ];
    }

    public function testResolveException()
    {
        $this->expectException(InvalidArgumentException::class);

        $resolved = $this->resolver->resolve('service2');
    }
}
