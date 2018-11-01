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
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBag;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AbstractTestBagTest extends TestCase
{
    private $testBag;

    public function setUp()
    {
        $this->testBag = $this->getMockForAbstractClass(AbstractTestBag::class);
    }

    public function testProviderClass()
    {
        $this->testBag->setProviderClass('foo');

        $this->assertSame('foo', $this->testBag->getProviderClass());
    }
}
