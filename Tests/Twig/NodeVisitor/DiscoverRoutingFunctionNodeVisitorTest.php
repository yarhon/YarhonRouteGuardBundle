<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Twig\NodeVisitor;

use Twig\Node\Node;
use Twig\Node\PrintNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Error\SyntaxError;
use Twig\TwigFunction;
use Yarhon\RouteGuardBundle\Tests\Twig\AbstractNodeTest;
use Yarhon\RouteGuardBundle\Twig\TokenParser\RouteTokenParser;
use Yarhon\RouteGuardBundle\Twig\Node\RouteNode;
use Yarhon\RouteGuardBundle\Twig\Node\RouteExpression;
use Yarhon\RouteGuardBundle\Twig\NodeVisitor\DiscoverRoutingFunctionNodeVisitor;

class DiscoverRoutingFunctionNodeVisitorTest extends AbstractNodeTest
{
    private $tagName = 'routewithdiscover';

    private $referenceVarName = 'ref';

    public function setUp()
    {
        parent::setUp();

        $this->environment->addFunction(new TwigFunction('url', function () {}));
        $this->environment->addFunction(new TwigFunction('path', function () {}));

        $nodeVisitor = new DiscoverRoutingFunctionNodeVisitor($this->referenceVarName, $this->tagName);
        $this->environment->addNodeVisitor($nodeVisitor);

        $this->environment->addTokenParser(new RouteTokenParser($this->tagName, true));
    }

    /**
     * @dataProvider discoverDataProvider
     */
    public function testDiscover($source, $expected)
    {
        $node = $this->parse($source);

        $this->assertEquals($expected, $node);
    }

    public function discoverDataProvider()
    {
        $nameExpression = new NameExpression($this->referenceVarName, 0);
        $nameExpression->setAttribute('always_defined', true);

        return [
            [
                // path function test
                '{% routewithdiscover discover %}{{ path("secure1") }}{% endroutewithdiscover %}',
                new RouteNode(
                    (new RouteExpression(
                        new Node([
                            new ConstantExpression('secure1', 0),
                        ])
                    ))->setGenerateAs('path'),
                    new PrintNode($nameExpression, 0)
                ),
            ],

            [
                // url function test
                '{% routewithdiscover discover %}{{ url("secure1") }}{% endroutewithdiscover %}',
                new RouteNode(
                    (new RouteExpression(
                        new Node([
                            new ConstantExpression('secure1', 0),
                        ])
                    ))->setGenerateAs('url'),
                    new PrintNode($nameExpression, 0)
                ),
            ],

            [
                // relative parameter test
                '{% routewithdiscover discover %}{{ path("secure1", {}, true) }}{% endroutewithdiscover %}',
                new RouteNode(
                    (new RouteExpression(
                        new Node([
                            new ConstantExpression('secure1', 0),
                            new ArrayExpression([], 0),
                            new ConstantExpression('GET', 0),
                        ])
                    ))->setGenerateAs('path', true),
                    new PrintNode($nameExpression, 0)
                ),
            ],
        ];
    }

    /**
     * @dataProvider discoverExceptionDataProvider
     */
    public function testDiscoverException($source, $expected)
    {
        $this->expectException($expected[0]);
        if (isset($expected[1])) {
            $this->expectExceptionMessage($expected[1]);
        }

        $this->parse($source);
    }

    public function discoverExceptionDataProvider()
    {
        return [
            [
                // without any routing function
                '{% routewithdiscover discover %}test{% endroutewithdiscover %}',
                [SyntaxError::class, sprintf('"%s" tag with discover option must contain one "url()" / "path()" call.', $this->tagName)],
            ],
            [
                // with 2 routing functions
                '{% routewithdiscover discover %}{{ url("secure1") }}{{ url("secure2") }}{% endroutewithdiscover %}',
                [SyntaxError::class, sprintf('"%s" tag with discover option must contain only one "url()" / "path()" call.', $this->tagName)],
            ],
        ];
    }
}
