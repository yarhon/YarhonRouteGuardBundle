<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Twig\TokenParser;

use Twig_Error_Syntax as SyntaxError;   // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Error\SyntaxError
use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\Node\PrintNode;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Yarhon\LinkGuardBundle\Tests\Twig\AbstractNodeTest;
use Yarhon\LinkGuardBundle\Twig\Node\RouteIfGrantedNode;
use Yarhon\LinkGuardBundle\Twig\Node\RouteIfGrantedExpression;

class RouteIfGrantedTokenParserTest extends AbstractNodeTest
{
    /**
     * @dataProvider parseDataProvider
     */
    public function testParse($source, $expected)
    {
        $node = $this->parse($source);

        $this->assertEquals($expected, $node);
    }

    public function parseDataProvider()
    {
        return [
            [
                // general test
                '{% routeifgranted ["secure1"] %}<a href="{{ route_reference }}">Link</a>{% endrouteifgranted %}',
                new RouteIfGrantedNode(
                    new RouteIfGrantedExpression(
                        new Node([
                            new ConstantExpression('secure1', 0)
                        ])
                    ),
                    new Node([
                        new TextNode('<a href="', 0),
                        new PrintNode(new NameExpression('route_reference', 0), 0),
                        new TextNode('">Link</a>', 0)
                    ])
                )
            ],

            [
                // else node test
                '{% routeifgranted ["secure1"] %}{% else %}else text{% endrouteifgranted %}',
                new RouteIfGrantedNode(
                    new RouteIfGrantedExpression(
                        new Node([
                            new ConstantExpression('secure1', 0)
                        ])
                    ),
                    new Node(),
                    new TextNode('else text', 0)
                )
            ],

            [
                // with "as"
                '{% routeifgranted ["secure1"] as path %}{% endrouteifgranted %}',
                new RouteIfGrantedNode(
                    (new RouteIfGrantedExpression(
                        new Node([
                            new ConstantExpression('secure1', 0)
                        ])
                    ))->setFunctionName('path')->setRelative(false),
                    new Node()
                )
            ],

            [
                // with "as"
                '{% routeifgranted ["secure1"] as path relative %}{% endrouteifgranted %}',
                new RouteIfGrantedNode(
                    (new RouteIfGrantedExpression(
                        new Node([
                            new ConstantExpression('secure1', 0)
                        ])
                    ))->setFunctionName('path')->setRelative(true),
                    new Node()
                )
            ],

            [
                // with "as"
                '{% routeifgranted ["secure1"] as path absolute %}{% endrouteifgranted %}',
                new RouteIfGrantedNode(
                    (new RouteIfGrantedExpression(
                        new Node([
                            new ConstantExpression('secure1', 0)
                        ])
                    ))->setFunctionName('path')->setRelative(false),
                    new Node()
                )
            ],

            [
                // with "as"
                '{% routeifgranted ["secure1"] as url %}{% endrouteifgranted %}',
                new RouteIfGrantedNode(
                    (new RouteIfGrantedExpression(
                        new Node([
                            new ConstantExpression('secure1', 0)
                        ])
                    ))->setFunctionName('url')->setRelative(false),
                    new Node()
                )
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
                '{% routeifgranted ["secure1"] %}{% end %}',
                [SyntaxError::class]
            ],
            [
                // without arguments and "discover"
                '{% routeifgranted %}{% endrouteifgranted %}',
                [SyntaxError::class]
            ],

            [
                // with "as" and no params
                '{% routeifgranted ["secure1"] as %}{% endrouteifgranted %}',
                [SyntaxError::class, '"name" expected with value "url" or "path"']
            ],

            [
                // with "as" and invalid function name
                '{% routeifgranted ["secure1"] as blabla %}{% endrouteifgranted %}',
                [SyntaxError::class, '"name" expected with value "url" or "path"']
            ],

            [
                // with "as" and invalid relative param
                '{% routeifgranted ["secure1"] as path blabla %}{% endrouteifgranted %}',
                [SyntaxError::class]
            ],
        ];
    }
}
