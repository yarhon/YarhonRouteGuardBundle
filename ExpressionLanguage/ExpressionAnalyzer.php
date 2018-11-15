<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\ExpressionLanguage\Node\Node;
use Symfony\Component\ExpressionLanguage\Node\NameNode;
use Symfony\Component\ExpressionLanguage\Node\GetAttrNode;
use Symfony\Component\ExpressionLanguage\Node\ConstantNode;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ExpressionAnalyzer
{
    /**
     * @param ParsedExpression $expression
     *
     * @return string[]
     */
    public function getUsedVariables(ParsedExpression $expression)
    {
        $variables = [];
        $this->collectVariables($expression->getNodes(), $variables);

        return array_unique($variables);
    }

    /**
     * @param ParsedExpression $expression
     * @param string           $variable
     *
     * @return string[]
     */
    public function getVariableAttributesCalls(ParsedExpression $expression, $variable)
    {
        $attributes = [];
        $this->collectVariableGetAttr($expression->getNodes(), $variable, $attributes);

        return array_unique($attributes);
    }

    /**
     * @param Node  $node
     * @param array $variables
     */
    private function collectVariables(Node $node, array &$variables)
    {
        if ($node instanceof NameNode) {
            $variables[] = $node->attributes['name'];
        }

        foreach ($node->nodes as $innerNode) {
            $this->collectVariables($innerNode, $variables);
        }
    }

    /**
     * @param Node   $node
     * @param string $variable
     * @param array  $attributes
     */
    private function collectVariableGetAttr(Node $node, $variable, array &$attributes)
    {
        if ($node instanceof GetAttrNode) {
            $nameNode = $node->nodes['node'];
            $attrNode = $node->nodes['attribute'];

            if ($nameNode->attributes['name'] === $variable && $attrNode instanceof ConstantNode) {
                $attributes[] = $attrNode->attributes['value'];
            }
        }

        foreach ($node->nodes as $innerNode) {
            $this->collectVariableGetAttr($innerNode, $variable, $attributes);
        }
    }
}
