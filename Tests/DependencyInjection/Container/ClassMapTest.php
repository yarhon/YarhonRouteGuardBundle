<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\DependencyInjection\Container;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ClassMap;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ClassMapTest extends TestCase
{
    public function testHas()
    {
        $map = new ClassMap(['test1' => 'class_name']);

        $this->assertTrue($map->has('test1'));
        $this->assertFalse($map->has('test2'));
    }

    public function testGet()
    {
        $map = new ClassMap(['test1' => 'class_name']);

        $this->assertEquals('class_name', $map->get('test1'));
    }

    public function testGetException()
    {
        $map = new ClassMap(['test1' => 'class_name']);

        $this->expectException(InvalidArgumentException::class);

        $map->get('test2');
    }
}
