<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Test;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagTest extends TestCase
{
    public function testGetTests()
    {
        $tests = [
            $this->createMock(TestInterface::class),
            $this->createMock(TestInterface::class),
        ];

        $testBag = new TestBag($tests);

        $this->assertSame($tests, $testBag->getTests());
    }
}
