<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Twig\NodeVisitor;

use Twig\Node\Node;
use Twig\Environment;
use Twig\NodeVisitor\AbstractNodeVisitor;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Error\SyntaxError;
use Yarhon\LinkGuardBundle\Twig\Node\LinkNode;
use Yarhon\LinkGuardBundle\Twig\Node\RouteExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * Note: we extend from AbstractNodeVisitor just for compatibility with Twig 1.x NodeVisitorInterface.
 * When this compatibility would no longer be needed, we could drop usage of AbstractNodeVisitor
 * and implement NodeVisitorInterface directly.
 *
 * TODO: find a way to set source context for thrown exceptions (see \Twig_Parser::parse)
 */
class DiscoverRoutingFunctionNodeVisitor extends AbstractNodeVisitor
{
    /**
     * @var array
     */
    private $discoverFunctions = [];

    /**
     * @var string
     */
    private $referenceVarName;

    /**
     * @var string
     */
    private $tagName;

    /**
     * @var Scope
     */
    private $scope;

    /**
     * DiscoverRoutingFunctionNodeVisitor constructor.
     *
     * @param array  $discoverFunctions
     * @param string $referenceVarName
     * @param string $tagName
     */
    public function __construct(array $discoverFunctions, $referenceVarName, $tagName)
    {
        $this->discoverFunctions = $discoverFunctions;
        $this->referenceVarName = $referenceVarName;
        $this->tagName = $tagName;
        $this->scope = new Scope();
    }

    /**
     * {@inheritdoc}
     */
    protected function doEnterNode(Node $node, Environment $env)
    {
        if ($this->isTargetNode($node)) {
            $this->scope = $this->scope->enter();
            $this->scope->set('insideTargetNode', true);
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SyntaxError If zero / more than one routing function calls was found inside node
     */
    protected function doLeaveNode(Node $node, Environment $env)
    {
        if ($this->isTargetNode($node)) {
            /* @var LinkNode $node */

            if (!$this->scope->has('routingFunction')) {
                throw new SyntaxError(
                    sprintf('"%s" tag with discover option must contain one %s call.', $this->tagName, $this->createDiscoverFunctionsString()),
                    $node->getTemplateLine()
                );
            }

            $condition = $this->createRouteExpression($this->scope->get('routingFunction'), $node->getTemplateLine());
            $node->setNode('condition', $condition);

            $this->scope->set('insideTargetNode', false);
            $this->scope = $this->scope->leave();

            return $node;
        }

        if ($this->scope->get('insideTargetNode') && $this->isDiscoveredFunctionNode($node)) {
            if ($this->scope->has('routingFunction')) {
                throw new SyntaxError(
                    sprintf('"%s" tag with discover option must contain only one %s call.', $this->tagName, $this->createDiscoverFunctionsString()),
                    $node->getTemplateLine()
                );
            }

            $this->scope->set('routingFunction', $node);

            $newNode = new NameExpression($this->referenceVarName, $node->getTemplateLine());
            $newNode->setAttribute('always_defined', true);

            return $newNode;
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * @param Node $node
     *
     * @return bool
     */
    private function isTargetNode(Node $node)
    {
        return $node instanceof LinkNode && !$node->hasNode('condition');
    }

    /**
     * @param Node $node
     *
     * @return bool
     */
    private function isDiscoveredFunctionNode(Node $node)
    {
        return $node instanceof FunctionExpression && in_array($node->getAttribute('name'), $this->discoverFunctions);
    }

    /**
     * @param FunctionExpression $function
     * @param int                $line
     *
     * @return RouteExpression
     *
     * @throws SyntaxError
     */
    private function createRouteExpression(FunctionExpression $function, $line)
    {
        $functionName = $function->getAttribute('name');
        $arguments = $function->getNode('arguments');
        $relativeNode = null;

        if ($arguments->hasNode(2)) {
            $relativeNode = $arguments->getNode(2);
            $arguments->removeNode(2);
        }

        $expression = new RouteExpression($arguments, $line);
        $expression->setFunctionName($functionName);

        if ($relativeNode) {
            $expression->setRelative($relativeNode);
        }

        return $expression;
    }

    private function createDiscoverFunctionsString()
    {
        $functions = $this->discoverFunctions;
        $functions = array_map(function ($name) {
            return '"'.$name.'()"';
        }, $functions);

        return implode(' / ', $functions);
    }
}
