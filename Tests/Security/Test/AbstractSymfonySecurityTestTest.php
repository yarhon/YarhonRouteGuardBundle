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
use Yarhon\RouteGuardBundle\Security\Test\AbstractSymfonySecurityTest;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AbstractSymfonySecurityTestTest extends TestCase
{
    public function testAttributes()
    {
        $test = $this->getMockForAbstractClass(AbstractSymfonySecurityTest::class, [['foo', 'bar']]);

        $this->assertSame(['foo', 'bar'], $test->getAttributes());
    }

    public function testSubject()
    {
        $test = $this->getMockForAbstractClass(AbstractSymfonySecurityTest::class, [[], 'foo']);

        $this->assertSame('foo', $test->getSubject());
    }
}
