<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Twig\Node;

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
    private static $variableName;

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
     * @param string $variableName
     */
    public static function setVariableName($variableName)
    {
        self::$variableName = $variableName;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SyntaxError
     * @throws RuntimeError
     */
    public function compile(Compiler $compiler)
    {
        // TODO: check if $variableName is not already defined in context

        $compiler->addDebugInfo($this);

        if (!$this->hasNode('condition')) {
            throw new SyntaxError('Condition node is required.', $this->getTemplateLine());
        }

        if (!self::$variableName) {
            throw new RuntimeError(
                sprintf('variableName is not set. setVariableName() method should be called before compiling.')
            );
        }

        $variable = new AssignNameExpression(self::$variableName, 0);

        $compiler
            ->subcompile($variable)
            ->write(" = array();\n")
            ->write('if (false !== (')
            ->subcompile($variable)
            ->write('["ref"] = ')
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
            ->subcompile($variable)
            ->write(");\n");
    }
}
