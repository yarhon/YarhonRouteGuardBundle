<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Twig\Node;

use Twig\Node\Node;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Error\SyntaxError;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteExpression extends FunctionExpression
{
    /**
     * RouteExpression constructor.
     *
     * @param Node $arguments
     * @param int  $line
     *
     * @throws SyntaxError
     */
    public function __construct(Node $arguments, $line = 0)
    {
        // TODO: validate arguments types?

        if ($arguments->count() < 1) {
            throw new SyntaxError('At least one argument (name) is required.', $line);
        }

        if ($arguments->count() > 3) {
            throw new SyntaxError('Unrecognized extra arguments, only 3 (name, parameters, method) allowed.', $line);
        }

        $this->addDefaultArguments($arguments);

        parent::__construct('route_guard_route', $arguments, $line);

        // Set default generateAs parameters.
        // We call this function after parent constructor call to allow it to rely on internal structure
        // created by parent constructor (i.e., call $this->getNode('arguments'), $this->getTemplateLine()).
        $this->setGenerateAs('path', false);
    }

    /**
     * TODO: allow to specify $referenceType as a constant (one of UrlGeneratorInterface constants).
     * Note that $relative can be an instance of AbstractExpression, and it's execution result can be non-calculable at compile time.
     *
     * @param string                  $referenceType
     * @param bool|AbstractExpression $relative
     *
     * @return self
     *
     * @throws SyntaxError
     */
    public function setGenerateAs($referenceType, $relative = false)
    {
        if (!in_array($referenceType, ['url', 'path'], true)) {
            throw new SyntaxError(sprintf('Invalid reference type: %s', $referenceType), $this->getTemplateLine());
        }

        $referenceType = new ConstantExpression($referenceType, $this->getTemplateLine());

        if (!($relative instanceof AbstractExpression)) {
            $relative = new ConstantExpression($relative, $this->getTemplateLine());
        }

        $argument = new ArrayExpression([], $this->getTemplateLine());
        $argument->addElement($referenceType);
        $argument->addElement($relative);

        $this->getNode('arguments')->setNode(3, $argument);

        return $this;
    }

    /**
     * @param Node $arguments
     */
    private function addDefaultArguments(Node $arguments)
    {
        $line = $arguments->getTemplateLine();

        if (1 === $arguments->count()) {
            // add a default "parameters" argument
            $arguments->setNode(1, new ArrayExpression([], $line));
        }

        if (2 === $arguments->count()) {
            // add a default "method" argument
            $arguments->setNode(2, new ConstantExpression('GET', $line));
        }
    }
}
