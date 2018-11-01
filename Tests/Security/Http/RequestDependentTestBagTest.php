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
use Yarhon\RouteGuardBundle\Security\Http\RequestDependentTestBag;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestConstraintInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestDependentTestBagTest extends TestCase
{
    private $testArrays;

    private $constraints;

    private $context;

    public function setUp()
    {
        $this->testArrays = [
            [$this->createMock(TestInterface::class)],
            [$this->createMock(TestInterface::class)],
        ];

        $this->constraints = [
            $this->createMock(RequestConstraintInterface::class),
            $this->createMock(RequestConstraintInterface::class),
        ];

        $this->context = $this->createMock(RequestContext::class);
    }

    public function testGetTestsWhenConstraintMatches()
    {
        $this->constraints[0]->method('matches')
            ->willReturn(false);

        $this->constraints[1]->method('matches')
            ->willReturn(true);

        $map = [
            [$this->testArrays[0], $this->constraints[0]],
            [$this->testArrays[1], $this->constraints[1]],
        ];

        $testBag = new RequestDependentTestBag($map);

        $this->assertSame($this->testArrays[1], $testBag->getTests($this->context));
    }

    public function testGetTestsWhenNoConstraintMatches()
    {
        $this->constraints[0]->method('matches')
            ->willReturn(false);

        $this->constraints[1]->method('matches')
            ->willReturn(false);

        $map = [
            [$this->testArrays[0], $this->constraints[0]],
            [$this->testArrays[1], $this->constraints[1]],
        ];

        $testBag = new RequestDependentTestBag($map);

        $this->assertEquals([], $testBag->getTests($this->context));
    }

    public function testGetTestsWhenConstraintIsNull()
    {
        $this->constraints[0]->method('matches')
            ->willReturn(false);

        $map = [
            [$this->testArrays[0], $this->constraints[0]],
            [$this->testArrays[1], null],
        ];

        $testBag = new RequestDependentTestBag($map);

        $this->assertSame($this->testArrays[1], $testBag->getTests($this->context));
    }
}
