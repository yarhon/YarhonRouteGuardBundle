<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Twig\NodeVisitor;

use Twig_Node as Node;               // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Node\Node
use Twig_Environment as Environment; // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Environment
use Twig\NodeVisitor\AbstractNodeVisitor;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\Error\SyntaxError;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\NameExpression;
use Yarhon\LinkGuardBundle\Twig\Node\LinkNode;
use Yarhon\LinkGuardBundle\Twig\Node\RouteExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * TODO: check if we need to use \Twig\NodeVisitor\AbstractNodeVisitor for compatibility with Twig 1.x
 * TODO: find a way to set source context for thrown exceptions (see \Twig_Parser::parse)
 */
class DiscoverRoutingFunctionNodeVisitor implements NodeVisitorInterface
{
    /**
     * @var Scope
     */
    private $scope;

    /**
     * @var array
     */
    private $routingFunctions = ['url', 'path'];

    public function __construct()
    {
        $this->scope = new Scope();
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node, Environment $env)
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
     * @throws SyntaxError If zero / more than one routing function call was found inside node
     */
    public function leaveNode(Node $node, Environment $env)
    {
        if ($this->isTargetNode($node)) {

            /** @var LinkNode $node */

            if (!$this->scope->has('routingFunction')) {
                throw new SyntaxError(
                    sprintf('"%s" tag with discover option must contain one url() or path() call.', LinkNode::TAG_NAME),
                    $node->getTemplateLine()
                );
            }

            $condition = $this->createRouteExpression($this->scope->get('routingFunction'), $node->getTemplateLine());
            $node->setNode('condition', $condition);

            $this->scope->set('insideTargetNode', false);
            $this->scope = $this->scope->leave();

            return $node;
        }

        if ($this->scope->get('insideTargetNode') && $this->isRoutingFunctionNode($node)) {

            if ($this->scope->has('routingFunction')) {
                throw new SyntaxError(
                    sprintf('"%s" tag with discover option must contain only one url() or path() call.', LinkNode::TAG_NAME),
                    $node->getTemplateLine()
                );
            }

            $this->scope->set('routingFunction', $node);

            $referenceVarName = LinkNode::getReferenceVarName();
            $newNode = new NameExpression($referenceVarName, $node->getTemplateLine());
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
    private function isRoutingFunctionNode(Node $node)
    {
        return $node instanceof FunctionExpression && in_array($node->getAttribute('name'), $this->routingFunctions);
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

        $condition = new RouteExpression($arguments, $line);
        $condition->setFunctionName($functionName);

        if ($relativeNode) {
            $condition->setRelative($relativeNode);
        }

        return $condition;
    }
}