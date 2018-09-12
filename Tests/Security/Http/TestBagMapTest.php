<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Http;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMap;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestConstraintInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagMapTest extends TestCase
{
    private $testBagOne;

    private $testBagTwo;

    private $constraintOne;

    private $constraintTwo;

    private $context;

    public function setUp()
    {
        $this->testBagOne = $this->createMock(TestBagInterface::class);
        $this->testBagTwo = $this->createMock(TestBagInterface::class);

        $this->constraintOne = $this->createMock(RequestConstraintInterface::class);
        $this->constraintTwo = $this->createMock(RequestConstraintInterface::class);

        $this->context = $this->createMock(RequestContext::class);
    }

    public function testConstruct()
    {
        $map = [
            [ $this->testBagOne, $this->constraintOne ],
            [ $this->testBagTwo, null ],
        ];

        $testBagMap = new TestBagMap($map);

        $this->assertAttributeEquals($map, 'elements', $testBagMap);
    }

    public function testResolveWhenConstraintMatches()
    {
        $this->constraintOne->method('matches')
            ->willReturn(false);

        $this->constraintTwo->method('matches')
            ->willReturn(true);

        $map = [
            [ $this->testBagOne, $this->constraintOne ],
            [ $this->testBagTwo, $this->constraintTwo ],
        ];

        $testBagMap = new TestBagMap($map);

        $this->assertSame($this->testBagTwo, $testBagMap->resolve($this->context));
    }

    public function testResolveWhenNoConstraintMatches()
    {
        $this->constraintOne->method('matches')
            ->willReturn(false);

        $this->constraintTwo->method('matches')
            ->willReturn(false);

        $map = [
            [ $this->testBagOne, $this->constraintOne ],
            [ $this->testBagTwo, $this->constraintTwo ],
        ];

        $testBagMap = new TestBagMap($map);

        $this->assertNull($testBagMap->resolve($this->context));
    }

    public function testResolveWhenConstraintIsNull()
    {
        $this->constraintOne->method('matches')
            ->willReturn(false);

        $map = [
            [ $this->testBagOne, $this->constraintOne ],
            [ $this->testBagTwo, null ],
        ];

        $testBagMap = new TestBagMap($map);

        $this->assertSame($this->testBagTwo, $testBagMap->resolve($this->context));
    }
}
