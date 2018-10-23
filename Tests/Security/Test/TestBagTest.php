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
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagTest extends TestCase
{
    public function testConstruct()
    {
        $tests = [
            $this->createMock(TestArguments::class),
            $this->createMock(TestArguments::class),
        ];

        $testBag = new TestBag($tests);

        $this->assertSame($tests, iterator_to_array($testBag));
    }
}
