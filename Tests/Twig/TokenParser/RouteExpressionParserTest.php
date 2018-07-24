<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Twig\TokenParser;

use Twig\Node\Node;
use Twig\Node\Expression\ConstantExpression;
use Twig\Error\SyntaxError;
use Yarhon\LinkGuardBundle\Tests\Twig\AbstractNodeTest;
use Yarhon\LinkGuardBundle\Twig\Node\LinkNode;
use Yarhon\LinkGuardBundle\Twig\Node\RouteExpression;

class RouteExpressionParserTest extends AbstractNodeTest
{
    /**
     * @dataProvider parseDataProvider
     */
    public function testParse($source, $expected)
    {
        $node = $this->parse($source);
        $node = $node->getNode('condition');

        $this->assertEquals($expected, $node);
    }

    public function parseDataProvider()
    {
        return [
            [
                '{% routeifgranted ["secure1"] as path %}{% endrouteifgranted %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setFunctionName('path')->setRelative(false),
            ],

            [
                '{% routeifgranted ["secure1"] as path relative %}{% endrouteifgranted %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setFunctionName('path')->setRelative(true),
            ],

            [
                '{% routeifgranted ["secure1"] as path absolute %}{% endrouteifgranted %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setFunctionName('path')->setRelative(false),
            ],

            [
                '{% routeifgranted ["secure1"] as url %}{% endrouteifgranted %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setFunctionName('url')->setRelative(false),
            ],
        ];
    }

    /**
     * @dataProvider parseExceptionDataProvider
     */
    public function testParseException($source, $expected)
    {
        $this->expectException($expected[0]);
        if (isset($expected[1])) {
            $this->expectExceptionMessage($expected[1]);
        }

        $this->parse($source);
    }

    public function parseExceptionDataProvider()
    {
        return [
            [
                // with "as" and no params
                '{% routeifgranted ["secure1"] as %}{% endrouteifgranted %}',
                [SyntaxError::class, '"name" expected with value "url" or "path"'],
            ],

            [
                // with "as" and invalid function name
                '{% routeifgranted ["secure1"] as blabla %}{% endrouteifgranted %}',
                [SyntaxError::class, '"name" expected with value "url" or "path"'],
            ],

            [
                // with "as" and invalid relative param
                '{% routeifgranted ["secure1"] as path blabla %}{% endrouteifgranted %}',
                [SyntaxError::class],
            ],
        ];
    }
}
