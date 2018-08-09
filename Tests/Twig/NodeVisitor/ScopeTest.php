<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Twig\NodeVisitor;

use PHPUnit\Framework\TestCase;
use Yarhon\LinkGuardBundle\Twig\NodeVisitor\Scope;

class ScopeTest extends TestCase
{
    public function testSetGet()
    {
        $scope = new Scope();
        $scope->set('a', 1);

        $this->assertTrue($scope->has('a'));
        $this->assertFalse($scope->has('b'));

        $this->assertEquals(1, $scope->get('a'));
        $this->assertEquals(2, $scope->get('b', 2));
        $this->assertNull($scope->get('b'));
    }

    public function testEnterLeave()
    {
        $scope = new Scope();
        $childScope = $scope->enter();

        $this->assertInstanceOf(Scope::class, $childScope);
        $this->assertNotSame($scope, $childScope);

        $parentScope = $childScope->leave();
        $this->assertInstanceOf(Scope::class, $parentScope);
        $this->assertSame($scope, $parentScope);
    }

    public function testSetLeft()
    {
        $scope = new Scope();
        $childScope = $scope->enter();
        $childScope->leave();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Left scope is not mutable.');

        $childScope->set('a', 5);
    }
}
