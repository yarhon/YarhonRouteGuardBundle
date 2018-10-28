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
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestArgumentsTest extends TestCase
{
    public function testAttributes()
    {
        $arguments = new TestArguments(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $arguments->getAttributes());
    }

    public function testSubject()
    {
        $arguments = new TestArguments([]);

        $arguments->setSubject('foo');
        $this->assertSame('foo', $arguments->getSubject());
    }

    public function testMetadata()
    {
        $arguments = new TestArguments([]);

        $self = $arguments->setMetadata('foo', 5);

        $this->assertSame($arguments, $self);

        $this->assertSame(5, $arguments->getMetadata('foo'));

        $this->assertNull($arguments->getMetadata('bar'));
    }
}
