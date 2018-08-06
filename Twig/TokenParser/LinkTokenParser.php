<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Twig\TokenParser;

use Twig\TokenParser\AbstractTokenParser;
use Twig\Token;
use Twig\TokenStream;
use Yarhon\LinkGuardBundle\Twig\Node\LinkNode;
use Yarhon\LinkGuardBundle\Twig\Node\RouteExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class LinkTokenParser extends AbstractTokenParser
{
    /**
     * @var string
     */
    private $tagName;

    /**
     * @var string
     */
    private $endTagName;

    /**
     * LinkTokenParser constructor.
     *
     * @param $tagName
     */
    public function __construct($tagName)
    {
        $this->tagName = $tagName;
        $this->endTagName = 'end'.$tagName;
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

        $stream->expect(Token::BLOCK_END_TYPE);

        $bodyNode = $parser->subparse(function (Token $token) {
            return $token->test(['else', $this->endTagName]);
        });

        if ('else' == $stream->next()->getValue()) {
            $stream->expect(Token::BLOCK_END_TYPE);

            // $dropNeedle parameter of subparse method is significant to call next() on the stream, that would skip the node with the end tag name.
            // For unknown reason, that node is skipped automatically if there are no any nested tags (i.e., {% else %}).
            // Same result could be achieved by the following code after subparse call:
            // $stream->expect($this->endTagName).
            // We use second option to be more explicit and to allow / disallow nested tags in the future.
            $elseNode = $parser->subparse(function (Token $token) {
                return $token->test([$this->endTagName]);
            });

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
}
