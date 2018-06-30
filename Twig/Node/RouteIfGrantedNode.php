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

use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\AssignNameExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteIfGrantedNode extends Node
{
    private static $referenceVarName;

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
        // TODO: check if $referenceVarName is not already defined in context

        $compiler->addDebugInfo($this);

        if (!$this->hasNode('mainExpression')) {
            throw new SyntaxError('mainExpression node is required.', $this->getTemplateLine());
        }

        $referenceVar = new AssignNameExpression(self::getReferenceVarName(), 0);

        $compiler
            ->write('if (false !== (')
            ->subcompile($referenceVar)
            ->write(' = ')
            ->subcompile($this->getNode('mainExpression'))
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

    public function setMainExpression(AbstractExpression $expression, array $generateAs = [])
    {
        if ($expression instanceof ArrayExpression) {
            $function = $this->createFunctionFromArgumentsArray($expression, $generateAs);
        } elseif($expression instanceof FunctionExpression) {
            $function = $this->createFunctionFromRoutingFunction($expression);
        } else {
            throw new SyntaxError(
                sprintf('Invalid main expression class: %s', get_class($expression)),
                $expression->getTemplateLine()
            );
        }

        $this->setNode('mainExpression', $function);
    }

    public static function setReferenceVarName($referenceVarName)
    {
        self::$referenceVarName = $referenceVarName;
    }

    public static function getReferenceVarName()
    {
        if (!self::$referenceVarName) {
            throw new \InvalidArgumentException(
                sprintf('%s::referenceVarName is not set. setReferenceVarName() method should be called first.', __CLASS__)
            );
        }

        return self::$referenceVarName;
    }

    private function createFunctionFromArgumentsArray(ArrayExpression $argumentsArray, array $generateAs)
    {
        $line = $argumentsArray->getTemplateLine();

        $arguments = new Node([], [], $line);
        for ($i = 1; $i < $argumentsArray->count(); $i += 2) {
            $arguments->setNode(floor($i / 2), $argumentsArray->getNode($i));
        }

        return $this->createFunction($arguments, $generateAs);
    }

    private function createFunctionFromRoutingFunction(FunctionExpression $routingFunction)
    {
        $arguments = $routingFunction->getNode('arguments');

        // process "relative" parameter (defaults to false)
        $relative = 'absolute';
        if ($arguments->hasNode(2)) {
            $relative = $arguments->getNode(2);
            $arguments->removeNode(2);
        }

        $generateAs = [$routingFunction->getAttribute('name'), $relative];

        return $this->createFunction($arguments, $generateAs);
    }

    private function createFunction(Node $arguments, array $generateAs)
    {
        // TODO: validate $generateAs

        $functionName = $generateAs[0] == 'url' ? 'url_if_granted' : 'path_if_granted';
        $relative = $generateAs[1];
        if (!($relative instanceof AbstractExpression)) {
            $relative = $relative == 'absolute' ? false : true;
        }

        $line = $arguments->getTemplateLine();

        if ($arguments->count() == 1) {
            // add a default "parameters" argument
            $arguments->setNode(1, new ArrayExpression([], $line));
        }

        if ($arguments->count() == 2) {
            // add a default "method" argument
            $arguments->setNode(2, new ConstantExpression('GET', $line));
        }

        if (!($relative instanceof AbstractExpression)) {
            $relative = new ConstantExpression($relative, $line);
        }

        // add a "relative" argument
        $arguments->setNode(3, $relative);

        return new FunctionExpression($functionName, $arguments, $line);
    }
}