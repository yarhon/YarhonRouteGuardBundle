<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Twig\Node;

use Twig\Compiler;
use Twig\Node\Node;
use Twig\Node\Expression\AssignNameExpression;
use Twig\Error\SyntaxError;
use Twig\Error\RuntimeError;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteNode extends Node
{
    /**
     * @var string
     */
    private static $referenceVarName;

    /**
     * RouteNode constructor.
     *
     * @param RouteExpression|null $condition
     * @param Node                 $bodyNode
     * @param Node|null            $elseNode
     * @param int                  $line
     */
    public function __construct(RouteExpression $condition = null, Node $bodyNode, Node $elseNode = null, $line = 0)
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

        parent::__construct($nodes, [], $line);
    }

    /**
     * @param string $referenceVarName
     */
    public static function setReferenceVarName($referenceVarName)
    {
        self::$referenceVarName = $referenceVarName;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SyntaxError
     * @throws RuntimeError
     */
    public function compile(Compiler $compiler)
    {
        // TODO: check if $referenceVarName is not already defined in context

        $compiler->addDebugInfo($this);

        if (!$this->hasNode('condition')) {
            throw new SyntaxError('Condition node is required.', $this->getTemplateLine());
        }

        if (!self::$referenceVarName) {
            throw new RuntimeError(
                sprintf('referenceVarName is not set. setReferenceVarName() method should be called before compiling.')
            );
        }

        $referenceVar = new AssignNameExpression(self::$referenceVarName, 0);

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
            ->write("}\n")
            ->write('unset(')
            ->subcompile($referenceVar)
            ->write(");\n");
    }
}
