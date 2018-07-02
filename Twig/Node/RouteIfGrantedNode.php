<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Twig\Node;

use Twig\Node\Node;
// use Twig\Compiler; // PhpStorm doesn't recognise this in type hints
use Twig_Compiler as Compiler;
use Twig\Error\SyntaxError;
use Twig\Node\Expression\AssignNameExpression;
use NeonLight\SecureLinksBundle\Twig\TokenParser\RouteIfGrantedTokenParser;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteIfGrantedNode extends Node
{
    /**
     * @var string
     */
    private static $referenceVarName;

    /**
     * RouteIfGrantedNode constructor.
     *
     * @param RouteIfGrantedExpression|null $condition
     * @param Node                          $bodyNode
     * @param Node|null                     $elseNode
     * @param int                           $line
     * @param string|null                   $tag
     */
    public function __construct(RouteIfGrantedExpression $condition = null, Node $bodyNode, Node $elseNode = null, $line = 0, $tag = null)
    {
        $nodes = [
            'body' => $bodyNode,
        ];

        if ($condition) {
            $nodes['condition'] = $condition;
        }

        if ($elseNode) {
            $nodes['else'] = $elseNode;
        }

        $tag = $tag ?: RouteIfGrantedTokenParser::TAG_NAME;

        parent::__construct($nodes, [], $line, $tag);
    }

    /**
     * {@inheritdoc}
     *
     * @throws SyntaxError
     */
    public function compile(Compiler $compiler)
    {
        // TODO: check if $referenceVarName is not already defined in context

        $compiler->addDebugInfo($this);

        if (!$this->hasNode('condition')) {
            throw new SyntaxError('Condition node is required.', $this->getTemplateLine());
        }

        $referenceVar = new AssignNameExpression(self::getReferenceVarName(), 0);

        $compiler
            ->write('if (false !== (')
            ->subcompile($referenceVar)
            ->write(' = ')
            ->subcompile($this->getNode('condition'))
            ->write(")) {\n")
            ->indent()
            ->subcompile($this->getNode('body'))
        ;

        if ($this->hasNode('else')) {
            $compiler
                ->outdent()
                ->write("} else {\n")
                ->indent()
                ->subcompile($this->getNode('else'))
            ;
        }

        $compiler
            ->outdent()
            ->write("}\n");
    }

    /**
     * @param string $referenceVarName
     */
    public static function setReferenceVarName($referenceVarName)
    {
        self::$referenceVarName = $referenceVarName;
    }

    /**
     * @return string
     *
     * @throws \LogicException
     */
    public static function getReferenceVarName()
    {
        if (!self::$referenceVarName) {
            throw new \LogicException(
                sprintf('%s::referenceVarName is not set. setReferenceVarName() method should be called first.', __CLASS__)
            );
        }

        return self::$referenceVarName;
    }
}