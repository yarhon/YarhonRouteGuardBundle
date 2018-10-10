<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Twig\TokenParser;

use Twig\TokenParser\AbstractTokenParser;
use Twig\Token;
use Twig\Parser;
use Yarhon\RouteGuardBundle\Twig\Node\RouteNode;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteTokenParser extends AbstractTokenParser
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
     * @var bool
     */
    private $allowDiscover;

    /**
     * @var RouteExpressionParser
     */
    private $expressionParser;

    /**
     * RouteTokenParser constructor.
     *
     * @param string $tagName
     * @param bool   $allowDiscover
     */
    public function __construct($tagName, $allowDiscover = false)
    {
        $this->tagName = $tagName;
        $this->endTagName = 'end'.$tagName;
        $this->allowDiscover = $allowDiscover;

        $this->expressionParser = new RouteExpressionParser();
    }

    /**
     * {@inheritdoc}
     */
    public function setParser(Parser $parser)
    {
        parent::setParser($parser);
        $this->expressionParser->setParser($parser);
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

        if ($this->allowDiscover && $stream->test('discover')) {
            $stream->next();
        } else {
            $condition = $this->expressionParser->parse($token);
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $bodyNode = $parser->subparse(function (Token $token) {
            return $token->test(['else', $this->endTagName]);
        });

        if ('else' == $stream->next()->getValue()) {
            $stream->expect(Token::BLOCK_END_TYPE);

            $elseNode = $parser->subparse(function (Token $token) {
                return $token->test([$this->endTagName]);
            });

            $stream->expect($this->endTagName);
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $node = new RouteNode($condition, $bodyNode, $elseNode, $token->getLine());

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
