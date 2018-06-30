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
use Twig\Environment;
use Twig\Parser;
use Twig\Source;
use Twig\Compiler;

use Twig\Node\Node;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\TextNode;
use Twig\Node\PrintNode;

use NeonLight\SecureLinksBundle\Twig\Extension\RoutingExtension;
use NeonLight\SecureLinksBundle\Twig\Node\RouteIfGrantedNode;

class RouteIfGrantedTokenParserTest extends TestCase
{
    /**
     * @var Environment
     */
    private $environment;

    public function setUp()
    {
        $loader = $this->getMockBuilder('Twig\Loader\LoaderInterface')->getMock();

        $this->environment = new Environment($loader, ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $this->environment->addExtension(new RoutingExtension());

        /*
        $routingExtension = $this->getMockBuilder(RoutingExtension::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->environment->addExtension($routingExtension);
        */

        $twigRoutingExtension = $this->getMockBuilder('Symfony\Bridge\Twig\Extension\RoutingExtension')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->environment->addExtension($twigRoutingExtension);

        /*
        $this->environment->addFunction(
            new TwigFunction(
                'url',
                function ($name, $parameters = array(), $schemeRelative = false) {
                    return null;
                }
            )
        );
        */
    }

    /**
     * @dataProvider getTestsForParse
     */
    public function testParse($source, $expected)
    {

        $source = new Source($source, '');
        $stream = $this->environment->tokenize($source);
        $parser = new Parser($this->environment);

        $node = $parser->parse($stream);
        $targetNode = $node->getNode('body')->getNode(0);

        $source = $this->compile($targetNode);

        var_dump((string) $targetNode);
        var_dump($source);

        // $this->assertEquals($expected, $targetNode);
    }

    public function compile($node)
    {
        $compiler = new Compiler($this->environment);
        $compiler->compile($node);
        $source = $compiler->getSource();

        return $source;
    }

    public function getTestsForParse()
    {
        return [
            [
                '{% routeifgranted discover %}<a href="{{ url("a1", { page: 10 }) }}">Test tag</a>{% else %}Not granted{% endrouteifgranted %}',

                //'{% routeifgranted url ["secure2", { page: 10 }, false, "GET"] %}<a href="{{ generatedUrl }}">Test tag</a>{% else %}Not granted{% endrouteifgranted %}',

                //'{% routeifgranted ["secure2", { page: 10 }, "GET"] as path %}<a href="{{ generatedUrl }}">Test tag</a>{% else %}Not granted{% endrouteifgranted %}',

                new RouteIfGrantedNode(
                new Node()
                /*
                    new NameExpression('form', 1),
                    new ArrayExpression(array(
                        new ConstantExpression(0, 1),
                        new ConstantExpression('tpl1', 1),
                    ), 1),
                    1,
                    'form_theme'
                */
                ),
            ],

            /*
            array(
                '{% form_theme form "tpl1" "tpl2" %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ArrayExpression(array(
                        new ConstantExpression(0, 1),
                        new ConstantExpression('tpl1', 1),
                        new ConstantExpression(1, 1),
                        new ConstantExpression('tpl2', 1),
                    ), 1),
                    1,
                    'form_theme'
                ),
            ),
            array(
                '{% form_theme form with "tpl1" %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ConstantExpression('tpl1', 1),
                    1,
                    'form_theme'
                ),
            ),
            array(
                '{% form_theme form with ["tpl1"] %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ArrayExpression(array(
                        new ConstantExpression(0, 1),
                        new ConstantExpression('tpl1', 1),
                    ), 1),
                    1,
                    'form_theme'
                ),
            ),
            array(
                '{% form_theme form with ["tpl1", "tpl2"] %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ArrayExpression(array(
                        new ConstantExpression(0, 1),
                        new ConstantExpression('tpl1', 1),
                        new ConstantExpression(1, 1),
                        new ConstantExpression('tpl2', 1),
                    ), 1),
                    1,
                    'form_theme'
                ),
            ),
            array(
                '{% form_theme form with ["tpl1", "tpl2"] only %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ArrayExpression(array(
                        new ConstantExpression(0, 1),
                        new ConstantExpression('tpl1', 1),
                        new ConstantExpression(1, 1),
                        new ConstantExpression('tpl2', 1),
                    ), 1),
                    1,
                    'form_theme',
                    true
                ),
            ),
            */
        ];
    }
}
