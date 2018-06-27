<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Twig\TokenParser;

use Twig\TokenParser\AbstractTokenParser;
//use Twig\Token; // PhpStorm doesn't recognise this in type hints
use Twig_Token as Token;
use Twig\Error\SyntaxError;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Parser;
use NeonLight\SecureLinksBundle\Twig\Node\IfRouteGrantedNode;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class IfRouteGrantedTokenParser extends AbstractTokenParser
{
    const TAG_NAME = 'ifroutegranted';

    const END_TAG_NAME = 'endifroutegranted';

    public function parse(Token $token)
    {
        // $stream->expect(Token::OPERATOR_TYPE, '=');

        $line = $token->getLine();

        $parser = $this->parser;
        $stream = $parser->getStream();

        $ifExpressionNode = null;
        if ($stream->nextIf(Token::NAME_TYPE, 'if')) {
            $ifExpressionNode = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $bodyNode = $parser->subparse(function(Token $token) {
            return $token->test(['else', self::END_TAG_NAME]);
        });

        // $this->testForNestedTag($stream); + add self::TAG_NAME to subparse stop condition

        if ('else' == $stream->next()->getValue()) {
            $stream->expect(Token::BLOCK_END_TYPE);

            /**
             * $dropNeedle parameter is significant to call next() on the stream, that would skip the node with the end tag name.
             * For unknown reason, that node is skipped automatically if there are no any nested tags (i.e., {% else %}).
             * Same result could be achieved by the following code after subparse call:
             * $stream->expect(self::END_TAG_NAME).
             * We use second option to be able to allow / disallow nested tags in future.
             */
            $elseNode = $parser->subparse(function(Token $token) {
                return $token->test([self::END_TAG_NAME]);
            });

            // $this->testForNestedTag($stream); + add self::TAG_NAME to subparse stop condition

            $stream->expect(self::END_TAG_NAME);
        } else {
            $elseNode = null;
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $isGrantedExpressionNode = $this->createFunctionExpressionNode('is_route_granted', ['ROLE_TEST_2'], $line);

        return new IfRouteGrantedNode($isGrantedExpressionNode, $bodyNode, $ifExpressionNode, $elseNode, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return self::TAG_NAME;
    }

    /*
    private function testForNestedTag($stream)
    {
        if ($stream->getCurrent()->test(self::TAG_NAME)) {
            throw new SyntaxError(
                sprintf('Nested "%s" tags are not allowed.', $this->getTag()),
                $stream->getCurrent()->getLine(), $stream->getSourceContext()
            );
        }
    }
    */

    private function createFunctionExpressionNode($name, array $arguments, $line)
    {
        $argumentNodes = [];

        foreach ($arguments as $argument) {
            $argumentNodes[] = new ConstantExpression($argument, $line);
        }

        $argumentNode = new Node($argumentNodes);
        $node = new FunctionExpression($name, $argumentNode, $line);

        return $node;
    }
}