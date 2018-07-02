<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Twig\TokenParser;

use Twig_Token as Token;              // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Token
use Twig_Error_Syntax as SyntaxError; // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Error\SyntaxError
use Twig\TokenParser\AbstractTokenParser;
use Twig\TokenStream;
use Twig\Node\Node;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\ArrayExpression;
use NeonLight\SecureLinksBundle\Twig\Node\RouteIfGrantedNode;
use NeonLight\SecureLinksBundle\Twig\Node\RouteIfGrantedExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteIfGrantedTokenParser extends AbstractTokenParser
{
    const TAG_NAME = 'routeifgranted';

    const END_TAG_NAME = 'endrouteifgranted';

    /**
     * {@inheritdoc}
     */
    public function parse(Token $token)
    {
        $condition = null;
        $elseNode = null;

        $parser = $this->parser;
        $stream = $parser->getStream();

        if (!$stream->test('discover')) {
            $arguments = $parser->getExpressionParser()->parseArrayExpression();
            $arguments = $this->arrayExpressionToArguments($arguments);
            $condition = new RouteIfGrantedExpression($arguments, $token->getLine());

            if ($stream->nextIf('as')) {
                // $functionName = $stream->expect(Token::NAME_TYPE, ['url', 'path'])->getValue();
                // Workaround for bug in Twig_TokenStream::expect() method. See self::streamExpect().
                $message = '"name" expected with value "url" or "path"';
                $functionName = $this->streamExpect($stream, Token::NAME_TYPE, ['url', 'path'], $message)->getValue();

                $condition->setFunctionName($functionName);

                if ($stream->test(['absolute', 'relative'])) {
                    $relative = $stream->getCurrent()->getValue() == 'absolute' ? false : true;
                    $condition->setRelative($relative);
                    $stream->next();
                }
            }
        } else {
            $stream->next();
        }

        /*
        if ($stream->nextIf('if')) {
            $parser->getExpressionParser()->parseExpression();
        }
        */

        $stream->expect(Token::BLOCK_END_TYPE);

        $bodyNode = $parser->subparse(function(Token $token) {
            return $token->test(['else', self::END_TAG_NAME]);
        });

        // $this->testForNestedTag($stream); + add self::TAG_NAME to subparse stop condition

        if ('else' == $stream->next()->getValue()) {
            $stream->expect(Token::BLOCK_END_TYPE);

            /*
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
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $node = new RouteIfGrantedNode($condition, $bodyNode, $elseNode, $token->getLine(), $this->getTag());

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return self::TAG_NAME;
    }

    /**
     * @param ArrayExpression $arrayExpression
     *
     * @return Node
     *
     * @throws SyntaxError
     */
    private function arrayExpressionToArguments(ArrayExpression $arrayExpression)
    {
        $line = $arrayExpression->getTemplateLine();
        $arguments = new Node([], [], $line);

        foreach ($arrayExpression->getKeyValuePairs() as $index => $pair) {
            $key = $pair['key'];

            if (!($key instanceof ConstantExpression) || $index !== $key->getAttribute('value')) {
                throw new SyntaxError('Arguments must be a zero-indexed array.', $line);
            }

            $arguments->setNode($index, $pair['value']);
        }

        return $arguments;
    }

    /**
     * Workaround for bug inside \Twig_TokenStream::expect.
     * In case of invalid template syntax, when exception is thrown, if type and/or value argument is an array,
     * an "Array to string conversion" error happens:
     * - for type because of:  Twig_Token::typeToEnglish($type)
     * - for value because of: sprintf(' with value "%s"', $value)
     *
     * @param TokenStream       $stream
     * @param array|int         $type
     * @param array|string|null $values
     * @param string|null       $message
     *
     * @return Token
     *
     * @throws SyntaxError
     */
    private function streamExpect(TokenStream $stream, $type, $values = null, $message = null)
    {
        $token = $stream->getCurrent();
        if (!$token->test($type, $values)) {
            if ($message) {
                $message = ' ('.$message.')';
            }

            throw new SyntaxError(sprintf('Unexpected token "%s" of value "%s"%s.',
                Token::typeToEnglish($token->getType()), $token->getValue(), $message),
                $token->getLine(),
                $stream->getSourceContext()
            );
        }
        $stream->next();

        return $token;
    }

    /*
    private function testForNestedTag(TokenStream $stream)
    {
        if (!$stream->getCurrent()->test(self::TAG_NAME)) {
            return;
        }

        throw new SyntaxError(
            sprintf('Nested "%s" tags are not allowed.', $this->getTag()),
            $stream->getCurrent()->getLine(), $stream->getSourceContext()
        );
    }
    */
}