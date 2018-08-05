<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Twig\NodeVisitor;

use Twig\Node\Node;
use Twig\Node\PrintNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Error\SyntaxError;
use Twig\TwigFunction;
use Yarhon\LinkGuardBundle\Tests\Twig\AbstractNodeTest;
use Yarhon\LinkGuardBundle\Twig\Node\LinkNode;
use Yarhon\LinkGuardBundle\Twig\Node\RouteExpression;
use Yarhon\LinkGuardBundle\Twig\NodeVisitor\DiscoverRoutingFunctionNodeVisitor;

class DiscoverRoutingFunctionNodeVisitorTest extends AbstractNodeTest
{
    private $linkTag = 'routeifgranted';

    private $referenceVarName = 'route_reference';

    public function setUp()
    {
        parent::setUp();

        $this->environment->addFunction(new TwigFunction('url', function () {}));
        $this->environment->addFunction(new TwigFunction('path', function () {}));

        $nodeVisitor = new DiscoverRoutingFunctionNodeVisitor(['url', 'path'], $this->referenceVarName, $this->linkTag);

        $this->environment->addNodeVisitor($nodeVisitor);
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
                '{% $linkTag discover %}{{ path("secure1") }}{% end$linkTag %}',
                new LinkNode(
                    (new RouteExpression(
                        new Node([
                            new ConstantExpression('secure1', 0),
                        ])
                    ))->setFunctionName('path'),
                    new PrintNode($nameExpression, 0)
                ),
            ],

            [
                // url function test
                '{% $linkTag discover %}{{ url("secure1") }}{% end$linkTag %}',
                new LinkNode(
                    (new RouteExpression(
                        new Node([
                            new ConstantExpression('secure1', 0),
                        ])
                    ))->setFunctionName('url'),
                    new PrintNode($nameExpression, 0)
                ),
            ],

            [
                // relative parameter test
                '{% $linkTag discover %}{{ path("secure1", {}, true) }}{% end$linkTag %}',
                new LinkNode(
                    (new RouteExpression(
                        new Node([
                            new ConstantExpression('secure1', 0),
                            new ArrayExpression([], 0),
                            new ConstantExpression('GET', 0),
                        ])
                    ))->setFunctionName('path')->setRelative(true),
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
                '{% $linkTag discover %}test{% end$linkTag %}',
                [SyntaxError::class, sprintf('"%s" tag with discover option must contain one "url()" / "path()" call.', $this->linkTag)],
            ],
            [
                // with 2 routing functions
                '{% $linkTag discover %}{{ url("secure1") }}{{ url("secure2") }}{% end$linkTag %}',
                [SyntaxError::class, sprintf('"%s" tag with discover option must contain only one "url()" / "path()" call.', $this->linkTag)],
            ],
        ];
    }
}
