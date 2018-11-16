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
use Twig\Node\TextNode;
use Twig\Node\PrintNode;
use Twig\Node\Expression\NameExpression;
use Twig\Error\SyntaxError;
use Yarhon\RouteGuardBundle\Tests\Twig\AbstractNodeTest;
use Yarhon\RouteGuardBundle\Twig\Node\RouteNode;
use Yarhon\RouteGuardBundle\Twig\TokenParser\RouteTokenParser;

class RouteTokenParserTest extends AbstractNodeTest
{
    public function testConstruct()
    {
        $tokenParser = new RouteTokenParser('foo');

        $this->assertEquals('foo', $tokenParser->getTag());
    }

    public function testGetTag()
    {
        $tokenParser = new RouteTokenParser('foo');
        $this->assertEquals('foo', $tokenParser->getTag());
    }

    /**
     * @dataProvider parseDataProvider
     */
    public function testParse($source, $expected)
    {
        $node = $this->parse($source);
        $node->removeNode('condition');

        $this->assertEquals($expected, $node);
    }

    public function parseDataProvider()
    {
        return [
            [
                // body node test
                '{% $tagName "secure1" %}<a href="{{ link }}">Link</a>{% end$tagName %}',
                new RouteNode(
                    null,
                    new Node([
                        new TextNode('<a href="', 0),
                        new PrintNode(new NameExpression('link', 0), 0),
                        new TextNode('">Link</a>', 0),
                    ])
                ),
            ],

            [
                // else node test
                '{% $tagName "secure1" %}{% else %}else text{% end$tagName %}',
                new RouteNode(
                    null,
                    new Node(),
                    new TextNode('else text', 0)
                ),
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
                // without end tag
                '{% $tagName "secure1" %}{% end %}',
                [SyntaxError::class],
            ],
            [
                // without arguments and "discover"
                '{% $tagName %}{% end$tagName %}',
                [SyntaxError::class],
            ],
        ];
    }
}
