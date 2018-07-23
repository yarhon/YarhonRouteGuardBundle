<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Twig\TokenParser;

use Twig_Token as Token;              // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Token
use Twig_Error_Syntax as SyntaxError; // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Error\SyntaxError
use Twig\TokenParser\AbstractTokenParser;
use Twig\TokenStream;
use Twig\Node\Node;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\ArrayExpression;
use Yarhon\LinkGuardBundle\Twig\Node\LinkNode;
use Yarhon\LinkGuardBundle\Twig\Node\RouteExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class LinkTokenParser extends AbstractTokenParser
{
    private $tagName;

    private $endTagName;

    // We don't use expressions in class constants in order to be compatible with PHP 5.5.
    public function __construct()
    {
        $this->tagName = LinkNode::TAG_NAME;
        $this->endTagName = 'end'.LinkNode::TAG_NAME;
    }

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
            $routeExpressionParser = new RouteExpressionParser($parser);
            $condition = $routeExpressionParser->parse($token);
        } else {
            $stream->next();
        }

        /*
        if ($stream->nextIf('if')) {
            $parser->getExpressionParser()->parseExpression();
        }
        */

        $stream->expect(Token::BLOCK_END_TYPE);

        $bodyNode = $parser->subparse(function (Token $token) {
            return $token->test(['else', $this->endTagName]);
        });

        // $this->testForNestedTag($stream); + add $this->tagName to subparse stop condition

        if ('else' == $stream->next()->getValue()) {
            $stream->expect(Token::BLOCK_END_TYPE);

            /*
             * $dropNeedle parameter is significant to call next() on the stream, that would skip the node with the end tag name.
             * For unknown reason, that node is skipped automatically if there are no any nested tags (i.e., {% else %}).
             * Same result could be achieved by the following code after subparse call:
             * $stream->expect($this->endTagName).
             * We use second option to be able to allow / disallow nested tags in future.
             */
            $elseNode = $parser->subparse(function (Token $token) {
                return $token->test([$this->endTagName]);
            });

            // $this->testForNestedTag($stream); + add $this->tagName to subparse stop condition

            $stream->expect($this->endTagName);
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $node = new LinkNode($condition, $bodyNode, $elseNode, $token->getLine());

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return $this->tagName;
    }

    /*
    private function testForNestedTag(TokenStream $stream)
    {
        if (!$stream->getCurrent()->test($this->tagName)) {
            return;
        }

        throw new SyntaxError(
            sprintf('Nested "%s" tags are not allowed.', $this->getTag()),
            $stream->getCurrent()->getLine(), $stream->getSourceContext()
        );
    }
    */
}
