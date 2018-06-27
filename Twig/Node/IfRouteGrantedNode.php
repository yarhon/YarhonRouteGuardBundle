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

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class IfRouteGrantedNode extends Node
{
    public function __construct(Node $isGrantedExpressionNode, Node $bodyNode, Node $ifExpressionNode = null, Node $elseNode = null, $line, $tag = null)
    {
        $nodes = [
            'body' => $bodyNode,
            'isGrantedExpression' => $isGrantedExpressionNode,
        ];

        if (null !== $elseNode) {
            $nodes['else'] = $elseNode;
        }

        parent::__construct($nodes, [], $line, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler->write('if (');

        $compiler->subcompile($this->getNode('isGrantedExpression'));

        $compiler
            ->raw(") {\n")
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
}