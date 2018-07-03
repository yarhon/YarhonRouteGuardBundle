<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Twig\Node;

use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    public function testAll()
    {
        /*
        $expression = new RouteIfGrantedExpression(new Node([
            new ConstantExpression('secure1', 0),
        ]));

        $this->assertEquals('path_if_granted', $expression->getAttribute('name'));

        $expression->setFunctionName('path');
        $this->assertEquals('path_if_granted', $expression->getAttribute('name'));

        $expression->setFunctionName('url');
        $this->assertEquals('url_if_granted', $expression->getAttribute('name'));

        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Invalid function name: blabla');

        $expression->setFunctionName('blabla');
        */
    }
}
