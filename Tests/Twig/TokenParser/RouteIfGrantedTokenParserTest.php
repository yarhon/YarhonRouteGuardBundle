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
        // TODO: get extension as a service, like in normal flow
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
        /*
         * Note: "name" (template name) parameter as null is significant for the private $name variable
         * of parsed Node instances to be propagated with null value.
         * Then properties of this instances would be strictly equal to properties
         * of Node instances provided by @dataProvider.
         */
        $source = new Source($source, null);
        $stream = $this->environment->tokenize($source);
        $parser = new Parser($this->environment);

        $node = $parser->parse($stream);
        $node = $node->getNode('body')->getNode(0);

        $target = $node->getNode('body');
        var_dump($expected == $target);
        $this->assertEquals($expected, $target);

        //$source = $this->compile($targetNode);

        //var_dump((string) $targetNode);
        //var_dump($source);


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
        //'{% routeifgranted url ["secure2", { page: 10 }, false, "GET"] %}<a href="{{ route_reference }}">Test tag</a>{% else %}Not granted{% endrouteifgranted %}',
        //'{% routeifgranted ["secure2", { page: 10 }, "GET"] as path %}<a href="{{ generatedUrl }}">Test tag</a>{% else %}Not granted{% endrouteifgranted %}',
        // '{% routeifgranted discover %}<a href="{{ url("a1", { page: 10 }) }}">Test tag</a>{% else %}Not granted{% endrouteifgranted %}',

        return [
            [
                '{% routeifgranted ["secure1"] %}<a href="{{ route_reference }}">Link</a>{% endrouteifgranted %}',
                //new RouteIfGrantedNode(
                    new Node([
                        new TextNode('<a href="', 1),
                        new PrintNode(new NameExpression('route_reference', 1), 1),
                        new TextNode('">Link</a>', 1)
                    ], [], 1)
                //)
            ],


        ];
    }
}
