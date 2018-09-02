<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Twig\TokenParser;

use Twig\Node\Node;
use Twig\Node\Expression\ConstantExpression;
use Twig\Error\SyntaxError;
use Yarhon\RouteGuardBundle\Tests\Twig\AbstractNodeTest;
use Yarhon\RouteGuardBundle\Twig\Node\RouteExpression;

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
                '{% $tagName ["secure1"] as path %}{% end$tagName %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setGenerateAs('path', false),
            ],
            [
                '{% $tagName ["secure1"] as path relative %}{% end$tagName %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setGenerateAs('path', true),
            ],

            [
                '{% $tagName ["secure1"] as path absolute %}{% end$tagName %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setGenerateAs('path', false),
            ],

            [
                '{% $tagName ["secure1"] as url %}{% end$tagName %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setGenerateAs('url', false),
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
                '{% $tagName ["secure1"] as %}{% end$tagName %}',
                [SyntaxError::class, '"name" expected with value "url" or "path"'],
            ],

            [
                // with "as" and invalid function name
                '{% $tagName ["secure1"] as blabla %}{% end$tagName %}',
                [SyntaxError::class, '"name" expected with value "url" or "path"'],
            ],

            [
                // with "as" and invalid relative param
                '{% $tagName ["secure1"] as path blabla %}{% end$tagName %}',
                [SyntaxError::class],
            ],
        ];
    }
}
