<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Twig_Error_Syntax as SyntaxError;   // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Error\SyntaxError
use Twig\Environment;
use Twig\Parser;
use Twig\Source;
use Twig\Compiler;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenStream;
use Yarhon\LinkGuardBundle\Twig\Extension\RoutingExtension;

abstract class AbstractNodeTest extends TestCase
{
    /**
     * @var Environment
     */
    private $environment;

    public function setUp()
    {
        $loader = $this->getMockBuilder('Twig\Loader\LoaderInterface')->getMock();

        $this->environment = new Environment($loader, ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);

        $this->environment->addExtension($this->getRoutingExtension());
        $this->environment->addExtension($this->getTwigBridgeRoutingExtension());
    }

    private function getRoutingExtension()
    {
        // TODO: get extension as a service, like in normal flow
        return new RoutingExtension();

        /*
        Alternative way:
        $routingExtension = $this->getMockBuilder(RoutingExtension::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        */
    }

    private function getTwigBridgeRoutingExtension()
    {
        $extension = $this->getMockBuilder('Symfony\Bridge\Twig\Extension\RoutingExtension')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        return $extension;

        /*
        Alternative way:
        $this->environment->addFunction(new TwigFunction('url', function() {}))
        */
    }

    /**
     * @param string $source
     *
     * @return Node
     *
     * @throws SyntaxError
     * @throws \ReflectionException
     */
    protected function parse($source)
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
         * Twig lexer will set line numbers starting from 1 (and only 1, if source is one-line string).
         * This sets line numbers of all tokens to 0 (default value in Node class constructor)
         * to allow skip non-required line number parameters in Nodes instances, created in dataProvider.
         */
        $this->hackLineNumbers($stream, 0);

        $parser = new Parser($this->environment);

        $node = $parser->parse($stream);
        $node = $node->getNode('body')->getNode(0);

        return $node;
    }

    /**
     * @param TokenStream $stream
     * @param $line
     *
     * @throws \ReflectionException
     */
    protected function hackLineNumbers(TokenStream $stream, $line)
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

    /**
     * @param Node $node
     *
     * @return string
     */
    protected function compile(Node $node)
    {
        $compiler = new Compiler($this->environment);
        $compiler->compile($node);
        $source = $compiler->getSource();

        return $source;
    }
}
