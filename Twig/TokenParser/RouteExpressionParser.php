<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Twig\TokenParser;

use Twig\Token;
use Twig\Parser;
use Twig\TokenStream;
use Twig\Node\Node;
use Twig\Node\Expression\ArrayExpression;
use Twig_Error_Syntax as SyntaxError; // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Error\SyntaxError
use Yarhon\LinkGuardBundle\Twig\Node\RouteExpression;

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
     * RouteExpressionParser constructor.
     *
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
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
        $parser = $this->parser;
        $stream = $parser->getStream();

        // or take token from arguments?
        $token = $stream->getCurrent();

        ///////////////////////////

        $arguments = $parser->getExpressionParser()->parseArrayExpression();
        $arguments = $this->arrayExpressionToArguments($arguments);
        $expression = new RouteExpression($arguments, $token->getLine());

        if ($stream->nextIf('as')) {
            // $functionName = $stream->expect(Token::NAME_TYPE, ['url', 'path'])->getValue();
            // Workaround for bug in Twig_TokenStream::expect() method. See self::streamExpect().
            $message = '"name" expected with value "url" or "path"';
            $functionName = $this->streamExpect($stream, Token::NAME_TYPE, ['url', 'path'], $message)->getValue();

            $expression->setFunctionName($functionName);

            if ($stream->test(['absolute', 'relative'])) {
                $relative = 'absolute' == $stream->getCurrent()->getValue() ? false : true;
                $expression->setRelative($relative);
                $stream->next();
            }
        }

        return $expression;
    }

    /**
     * @param ArrayExpression $arrayExpression
     *
     * @return Node
     */
    private function arrayExpressionToArguments(ArrayExpression $arrayExpression)
    {
        $line = $arrayExpression->getTemplateLine();
        $arguments = new Node([], [], $line);

        foreach ($arrayExpression->getKeyValuePairs() as $index => $pair) {
            $arguments->setNode($index, $pair['value']);
        }

        return $arguments;
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
