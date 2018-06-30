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
use Twig\TokenStream;
use NeonLight\SecureLinksBundle\Twig\Node\RouteIfGrantedNode;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteIfGrantedTokenParser extends AbstractTokenParser
{
    const TAG_NAME = 'routeifgranted';

    const END_TAG_NAME = 'endrouteifgranted';

    public function parse(Token $token)
    {
        $subNodes = [];
        $discoverRoutingFunction = false;
        $generateAs = ['url', 'absolute'];

        $parser = $this->parser;
        $stream = $parser->getStream();

        if (!$stream->test('discover')) {
            $arguments = $parser->getExpressionParser()->parseArrayExpression();

            if ($arguments->count() < 1 || $arguments->count() > 3) {
                // TODO: throw an exception
            }

            if ($stream->nextIf('as')) {
                // $generateAs[0] = $stream->expect(Token::NAME_TYPE, ['url', 'path'])->getValue();
                // Workaround for bug in Twig_TokenStream::expect() method. See self::streamExpect().
                $message = '"name" expected with value "url" or "path"';
                $generateAs[0] = $this->streamExpect($stream, Token::NAME_TYPE, ['url', 'path'], $message)->getValue();

                if ($stream->test(['absolute', 'relative'])) {
                    $generateAs[1] = $stream->getCurrent()->getValue();
                    $stream->next();
                }
            }
        } else {
            $discoverRoutingFunction = true;
            $stream->next();
        }

        /*
        if ($stream->nextIf('if')) {
            $subNodes['ifExpression'] = $parser->getExpressionParser()->parseExpression();
        }
        */

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
            $subNodes['else'] = $parser->subparse(function(Token $token) {
                return $token->test([self::END_TAG_NAME]);
            });

            // $this->testForNestedTag($stream); + add self::TAG_NAME to subparse stop condition

            $stream->expect(self::END_TAG_NAME);
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $node = new RouteIfGrantedNode($bodyNode, $token->getLine(), $this->getTag());

        if (!$discoverRoutingFunction) {
            $node->setMainExpression($arguments, $generateAs);
        } else {
            $node->setAttribute('discover', true);
        }

        foreach ($subNodes as $name => $subNode) {
            $node->setNode($name, $subNode);
        }

        return $node;
    }

    public function getTag()
    {
        return self::TAG_NAME;
    }

    /**
     * Workaround for twig bug inside \Twig_TokenStream::expect.
     * In case of invalid template syntax, when exception is thrown, if type and/or value argument is an array,
     * an "Array to string conversion" error happens:
     * - for type because of:  Twig_Token::typeToEnglish($type)
     * - for value because of: sprintf(' with value "%s"', $value)
     *
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
}