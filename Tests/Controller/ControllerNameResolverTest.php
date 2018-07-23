<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Yarhon\LinkGuardBundle\Controller\ControllerNameResolver;
use Yarhon\LinkGuardBundle\Tests\Fixtures\Controller\SimpleController;

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
                'Yarhon\LinkGuardBundle\Tests\Fixtures\Controller\SimpleController::index',
            ],
            [
                [new SimpleController(), 'index'],
                'Yarhon\LinkGuardBundle\Tests\Fixtures\Controller\SimpleController::index',
            ],
            [
                new SimpleController(),
                'Yarhon\LinkGuardBundle\Tests\Fixtures\Controller\SimpleController::__invoke',
            ],
            [
                'array_map',
                false,
            ],
            [
                'Yarhon\LinkGuardBundle\Tests\Fixtures\Controller\SimpleController',
                'Yarhon\LinkGuardBundle\Tests\Fixtures\Controller\SimpleController::__invoke',
            ],
            [
                'Yarhon\LinkGuardBundle\Tests\Fixtures\Controller\SimpleController::index',
                'Yarhon\LinkGuardBundle\Tests\Fixtures\Controller\SimpleController::index',
            ],
        ];
    }

    public function testResolveException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $resolved = $this->resolver->resolve([]);
    }
}
