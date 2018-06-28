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

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class IfRouteGrantedNode extends Node
{
    public function __construct(Node $bodyNode, $line = 0, $tag = null)
    {
        $nodes = [
            'body' => $bodyNode,
        ];

        parent::__construct($nodes, [], $line, $tag);
    }

    /**
     * {@inheritdoc}
     *
     * @throws SyntaxError
     */
    public function compile(Compiler $compiler)
    {
        // TODO: check if $varName is not already defined in context

        $compiler->addDebugInfo($this);

        if (!$this->hasNode('isGrantedExpression')) {
            throw new SyntaxError('isGrantedExpression node is required.', $this->getTemplateLine());
        }

        $varName = 'generatedUrl';

        $compiler
            ->write(sprintf('if (false !== ($context["%s"] = ', $varName))
            ->subcompile($this->getNode('isGrantedExpression'))
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
}