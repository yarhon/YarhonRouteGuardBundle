<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Twig\NodeVisitor;

use Twig\NodeVisitor\AbstractNodeVisitor;
use Twig\NodeVisitor\NodeVisitorInterface;
// use Twig\Node\Node; // PhpStorm doesn't recognise this in type hints
use Twig_Node as Node;
//use Twig\Environment; // PhpStorm doesn't recognise this in type hints
use Twig_Environment as Environment;
use Twig\Node\Expression\FunctionExpression;
use Twig\Error\SyntaxError;
use NeonLight\SecureLinksBundle\Twig\Node\IfRouteGrantedNode;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * TODO: check if we need to use \Twig\NodeVisitor\AbstractNodeVisitor for compatibility with Twig 1.x
 * TODO: find a way to set source context for thrown exceptions (see \Twig_Parser::parse)
 */
class RoutingFunctionNodeVisitor implements NodeVisitorInterface
{
    private $scope;

    private $routingFunctions = ['url', 'path'];

    public function __construct()
    {
        $this->scope = new Scope();
    }

    /**
     * {@inheritdoc}
     *
     * @throws SyntaxError If more than one routing function calls were found inside node
     */
    public function enterNode(Node $node, Environment $env)
    {
        if ($node instanceof IfRouteGrantedNode) {
            $this->scope = $this->scope->enter();
            $this->scope->set('insideTargetNode', true);

            return $node;
        }

        if ($this->scope->get('insideTargetNode') && $this->isRoutingFunction($node)) {

            if ($this->scope->has('routingFunction')) {
                throw new SyntaxError(
                    '"ifRouteGranted" tag with auto discovery must contain only one url() or path() call.',
                    $node->getTemplateLine()
                );
            }

            $this->scope->set('routingFunction', $node);

            return false;
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SyntaxError If no routing function call was found inside node
     */
    public function leaveNode(Node $node, Environment $env)
    {
        if ($node instanceof IfRouteGrantedNode) {

            if (!$this->scope->has('routingFunction')) {
                throw new SyntaxError(
                    '"ifRouteGranted" tag with auto discovery must contain one url() or path() call.',
                    $node->getTemplateLine()
                );
            }

            // !!!! fix this
            //$node->setAttribute('routingFunction', $this->scope->get('routingFunction'));

            $this->scope->set('insideTargetNode', false);
            $this->scope = $this->scope->leave();
        }

        return $node;
    }

    public function getPriority()
    {
        return 0;
    }

    private function isRoutingFunction(Node $node)
    {
        return $node instanceof FunctionExpression && in_array($node->getAttribute('name'), $this->routingFunctions);
    }
}