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
use Twig\Node\Expression\ArrayExpression;
use Twig_Error_Syntax as SyntaxError; // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Error\SyntaxError
use Yarhon\RouteGuardBundle\Twig\Node\RouteExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * TODO: implement \Twig\TokenParser\TokenParserInterface ?
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
        $parser = $this->parser;
        $stream = $parser->getStream();

        $arguments = $parser->getExpressionParser()->parseArrayExpression();
        $arguments = $this->arrayExpressionToArguments($arguments);
        $expression = new RouteExpression($arguments, $token->getLine());

        if ($stream->nextIf('as')) {
            // $functionName = $stream->expect(Token::NAME_TYPE, ['url', 'path'])->getValue();
            // Workaround for bug in Twig_TokenStream::expect() method. See self::streamExpect().
            $message = '"name" expected with value "url" or "path"';
            $referenceType = $this->streamExpect($stream, Token::NAME_TYPE, ['url', 'path'], $message)->getValue();
            $generateAs = [$referenceType];

            if ($stream->test(['absolute', 'relative'])) {
                $relative = 'absolute' !== $stream->getCurrent()->getValue();
                $generateAs[] = $relative;
                $stream->next();
            }

            $expression->setGenerateAs(...$generateAs);
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
