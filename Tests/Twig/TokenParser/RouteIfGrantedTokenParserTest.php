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
//use Twig\Error\SyntaxError;
use Twig_Error_Syntax as SyntaxError;
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
use Twig\Token;
use Twig\TokenStream;

use NeonLight\SecureLinksBundle\Twig\Extension\RoutingExtension;
use NeonLight\SecureLinksBundle\Twig\Node\RouteIfGrantedNode;
use NeonLight\SecureLinksBundle\Twig\Node\RouteIfGrantedExpression;

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
         * of parsed Node instances to be propagated with null value (it's default value).
         * Then properties of this instances would be strictly equal to properties
         * of Node instances provided by @dataProvider.
         */
        $source = new Source($source, null);
        $stream = $this->environment->tokenize($source);

        /*
         * Twig lexer will set line numbers starting from 1 (and only 1 source is one-line).
         * This sets line numbers of all tokens to 0 (default value)
         * to allow skip non-required line number parameters in Nodes instances, created in dataProvider.
         */
        $this->hackLineNumbers($stream, 0);

        $parser = new Parser($this->environment);

        $node = $parser->parse($stream);
        $node = $node->getNode('body')->getNode(0);

        $this->assertEquals($expected, $node);

        //var_dump((string) $node);
        //$source = $this->compile($node);
        //var_dump($source);
    }

    /**
     * @param TokenStream $stream
     * @param $line
     *
     * @throws \ReflectionException
     */
    private function hackLineNumbers(TokenStream $stream, $line)
    {
        $tokens = [];

        $i = 1;
        while (true) {
            try {
                $tokens[] = $stream->look($i);
                $i++;
            } catch (SyntaxError $e) {
                break;
            }
        }

        $property = new \ReflectionProperty(Token::class, 'lineno');
        $property->setAccessible(true);

        foreach ($tokens as $token) {
            $property->setValue($token, $line);
        }
    }

    public function compile($node)
    {
        $compiler = new Compiler($this->environment);
        $compiler->compile($node);
        $source = $compiler->getSource();

        return $source;
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
