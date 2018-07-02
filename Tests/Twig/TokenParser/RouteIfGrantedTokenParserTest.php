<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Tests\Twig\TokenParser;

use PHPUnit\Framework\TestCase;

//use Twig\Error\SyntaxError;
use Twig_Error_Syntax as SyntaxError;

use Twig\Node\Node;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\TextNode;
use Twig\Node\PrintNode;

use NeonLight\SecureLinksBundle\Tests\Twig\AbstractNodeTest;
use NeonLight\SecureLinksBundle\Twig\Extension\RoutingExtension;
use NeonLight\SecureLinksBundle\Twig\Node\RouteIfGrantedNode;
use NeonLight\SecureLinksBundle\Twig\Node\RouteIfGrantedExpression;

class RouteIfGrantedTokenParserTest extends AbstractNodeTest
{
    /**
     * @dataProvider getTestsForParse
     */
    public function testParse($source, $expected)
    {
        $node = $this->parse($source);

        $this->assertEquals($expected, $node);

        //var_dump((string) $node);
        //$source = $this->compile($node);
        //var_dump($source);
    }

    /**
     * @return array
     *
     * @throws \Twig\Error\SyntaxError
     */
    public function getTestsForParse()
    {
        //'{% routeifgranted url ["secure2", { page: 10 }, false, "GET"] %}<a href="{{ route_reference }}">Test tag</a>{% else %}Not granted{% endrouteifgranted %}',
        //'{% routeifgranted ["secure2", { page: 10 }, "GET"] as path %}<a href="{{ generatedUrl }}">Test tag</a>{% else %}Not granted{% endrouteifgranted %}',
        // '{% routeifgranted discover %}<a href="{{ url("a1", { page: 10 }) }}">Test tag</a>{% else %}Not granted{% endrouteifgranted %}',

        return [
            [
                '{% routeifgranted ["secure1"] %}<a href="{{ route_reference }}">Link</a>{% endrouteifgranted %}',
                new RouteIfGrantedNode(
                    (new RouteIfGrantedExpression(
                        new Node([
                            new ConstantExpression('secure1', 0)
                        ])
                    ))->setFunctionName('path'),
                    new Node([
                        new TextNode('<a href="', 0),
                        new PrintNode(new NameExpression('route_reference', 0), 0),
                        new TextNode('">Link</a>', 0)
                    ])
                )
            ],
        ];
    }
}
