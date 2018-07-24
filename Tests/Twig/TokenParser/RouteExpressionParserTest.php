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
use Twig\Node\Expression\ConstantExpression;
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
     * @expectedException \Twig\Error\SyntaxError
     * @expectedExceptionMessage "name" expected with value "url" or "path"
     */
    public function testParseExceptionWithGenerateAsAndNoParams()
    {
        $source = '{% routeifgranted ["secure1"] as %}{% endrouteifgranted %}';
        $this->parse($source);
    }

    /**
     * @expectedException \Twig\Error\SyntaxError
     * @expectedExceptionMessage "name" expected with value "url" or "path"
     */
    public function testParseExceptionWithGenerateAsAndInvalidFunctionName()
    {
        $source = '{% routeifgranted ["secure1"] as blabla %}{% endrouteifgranted %}';
        $this->parse($source);
    }

    /**
     * @expectedException \Twig\Error\SyntaxError
     */
    public function testParseExceptionWithGenerateAsAndInvalidRelativeParam()
    {
        $source = '{% routeifgranted ["secure1"] as path blabla %}{% endrouteifgranted %}';
        $this->parse($source);
    }
}
