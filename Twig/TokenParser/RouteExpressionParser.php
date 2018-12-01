<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Twig\TokenParser;

use Twig\Token;
use Twig\Parser;
use Twig\TokenStream;
use Twig\Node\Node;
use Twig_Error_Syntax as SyntaxError; // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Error\SyntaxError
use Yarhon\RouteGuardBundle\Twig\Node\RouteExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteExpressionParser
{
    /**
     * @var Parser;
     */
    private $parser;

    /**
     * @param Parser $parser
     */
    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param Token $token
     *
     * @return RouteExpression
     *
     * @throws SyntaxError
     */
    public function parse(Token $token)
    {
        $routeContextArguments = $this->parseRouteContextArguments();
        $expression = new RouteExpression($routeContextArguments, $token->getLine());

        if ($generateAs = $this->parseGenerateAs()) {
            $expression->setGenerateAs(...$generateAs);
        }

        return $expression;
    }

    private function parseRouteContextArguments()
    {
        $stream = $this->parser->getStream();
        $line = $stream->getCurrent()->getLine();

        $arguments = [];
        while (!$stream->test(Token::BLOCK_END_TYPE) && !$stream->test('as')) {
            if (count($arguments)) {
                $stream->expect(Token::PUNCTUATION_TYPE, ',', 'Arguments must be separated by a comma');
            }

            $arguments[] = $this->parser->getExpressionParser()->parseExpression();
        }

        return new Node($arguments, [], $line);
    }

    private function parseGenerateAs()
    {
        $stream = $this->parser->getStream();

        $generateAs = [];

        if ($stream->nextIf('as')) {
            // $functionName = $stream->expect(Token::NAME_TYPE, ['url', 'path'])->getValue();
            // Workaround for bug in Twig_TokenStream::expect() method. See self::streamExpect().
            $message = '"name" expected with value "url" or "path"';
            $referenceType = $this->streamExpect($stream, Token::NAME_TYPE, ['url', 'path'], $message)->getValue();
            $generateAs[] = $referenceType;

            if ($stream->test(['absolute', 'relative'])) {
                $relative = 'absolute' !== $stream->getCurrent()->getValue();
                $generateAs[] = $relative;
                $stream->next();
            }
        }

        return $generateAs;
    }

    /**
     * Workaround for bug inside \Twig_TokenStream::expect.
     * In case of invalid template syntax, when exception is thrown, if type and/or value argument is an array,
     * an "Array to string conversion" error happens:
     * - for type because of:  Twig_Token::typeToEnglish($type)
     * - for value because of: sprintf(' with value "%s"', $value).
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
}
