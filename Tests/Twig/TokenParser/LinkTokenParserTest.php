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
use Twig\Node\TextNode;
use Twig\Node\PrintNode;
use Twig\Node\Expression\NameExpression;
use Twig\Error\SyntaxError;
use Yarhon\LinkGuardBundle\Tests\Twig\AbstractNodeTest;
use Yarhon\LinkGuardBundle\Twig\Node\LinkNode;

class LinkTokenParserTest extends AbstractNodeTest
{
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
                '{% $linkTag ["secure1"] %}<a href="{{ route_reference }}">Link</a>{% end$linkTag %}',
                new LinkNode(
                    null,
                    new Node([
                        new TextNode('<a href="', 0),
                        new PrintNode(new NameExpression('route_reference', 0), 0),
                        new TextNode('">Link</a>', 0),
                    ])
                ),
            ],

            [
                // else node test
                '{% $linkTag ["secure1"] %}{% else %}else text{% end$linkTag %}',
                new LinkNode(
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
                '{% $linkTag ["secure1"] %}{% end %}',
                [SyntaxError::class],
            ],
            [
                // without arguments and "discover"
                '{% $linkTag %}{% end$linkTag %}',
                [SyntaxError::class],
            ],
        ];
    }
}
