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
use Yarhon\RouteGuardBundle\Security\Test\IsGrantedTest;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class IsGrantedTestTest extends TestCase
{
    public function testAttributes()
    {
        $test = new IsGrantedTest(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $test->getAttributes());
    }

    public function testSubject()
    {
        $test = new IsGrantedTest([], 'foo');

        $this->assertSame('foo', $test->getSubject());
    }

    public function testMetadata()
    {
        $test = new IsGrantedTest([]);

        $self = $test->setMetadata('foo', 5);

        $this->assertSame($test, $self);

        $this->assertSame(5, $test->getMetadata('foo'));

        $this->assertNull($test->getMetadata('bar'));
    }
}
