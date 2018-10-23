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
    private $testBags;

    private $constraints;

    private $context;

    public function setUp()
    {
        $this->testBags = [
            $this->createMock(TestBagInterface::class),
            $this->createMock(TestBagInterface::class),
        ];

        $this->constraints = [
            $this->createMock(RequestConstraintInterface::class),
            $this->createMock(RequestConstraintInterface::class),
        ];

        $this->context = $this->createMock(RequestContext::class);
    }

    public function testConstruct()
    {
        $map = [
            [$this->testBags[0], $this->constraints[0]],
            [$this->testBags[1], null],
        ];

        $testBagMap = new TestBagMap($map);

        $this->assertEquals($map, iterator_to_array($testBagMap));
    }

    public function testResolveWhenConstraintMatches()
    {
        $this->constraints[0]->method('matches')
            ->willReturn(false);

        $this->constraints[1]->method('matches')
            ->willReturn(true);

        $map = [
            [$this->testBags[0], $this->constraints[0]],
            [$this->testBags[1], $this->constraints[1]],
        ];

        $testBagMap = new TestBagMap($map);

        $this->assertSame($this->testBags[1], $testBagMap->resolve($this->context));
    }

    public function testResolveWhenNoConstraintMatches()
    {
        $this->constraints[0]->method('matches')
            ->willReturn(false);

        $this->constraints[1]->method('matches')
            ->willReturn(false);

        $map = [
            [$this->testBags[0], $this->constraints[0]],
            [$this->testBags[1], $this->constraints[1]],
        ];

        $testBagMap = new TestBagMap($map);

        $this->assertNull($testBagMap->resolve($this->context));
    }

    public function testResolveWhenConstraintIsNull()
    {
        $this->constraints[0]->method('matches')
            ->willReturn(false);

        $map = [
            [$this->testBags[0], $this->constraints[0]],
            [$this->testBags[1], null],
        ];

        $testBagMap = new TestBagMap($map);

        $this->assertSame($this->testBags[1], $testBagMap->resolve($this->context));
    }
}
