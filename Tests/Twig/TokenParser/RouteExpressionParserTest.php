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
                '{% $linkTag ["secure1"] as path %}{% end$linkTag %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setGenerateAs('path', false),
            ],
            [
                '{% $linkTag ["secure1"] as path relative %}{% end$linkTag %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setGenerateAs('path', true),
            ],

            [
                '{% $linkTag ["secure1"] as path absolute %}{% end$linkTag %}',
                (new RouteExpression(
                    new Node([
                        new ConstantExpression('secure1', 0),
                    ])
                ))->setGenerateAs('path', false),
            ],

            [
                '{% $linkTag ["secure1"] as url %}{% end$linkTag %}',
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
                '{% $linkTag ["secure1"] as %}{% end$linkTag %}',
                [SyntaxError::class, '"name" expected with value "url" or "path"'],
            ],

            [
                // with "as" and invalid function name
                '{% $linkTag ["secure1"] as blabla %}{% end$linkTag %}',
                [SyntaxError::class, '"name" expected with value "url" or "path"'],
            ],

            [
                // with "as" and invalid relative param
                '{% $linkTag ["secure1"] as path blabla %}{% end$linkTag %}',
                [SyntaxError::class],
            ],
        ];
    }
}
