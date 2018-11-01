<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\Expression;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ExpressionDecorator
{
    /**
     * @var Expression;
     */
    private $expression;

    /**
     * @var array
     */
    private $variableNames;

    /**
     * @var array
     */
    private $variables = [];

    /**
     * @param Expression $expression
     * @param array      $variableNames
     */
    public function __construct(Expression $expression, array $variableNames = [])
    {
        $this->expression = $expression;
        $this->variableNames = $variableNames;
    }

    /**
     * @return Expression
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @return array
     */
    public function getVariableNames()
    {
        return $this->variableNames;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     */
    public function setVariables(array $variables)
    {
        $missed = array_diff($this->variableNames, array_keys($variables));

        if (count($missed)) {
            throw new InvalidArgumentException(sprintf('Missed variables: "%s".', implode('", "', $missed)));
        }

        $unknown = array_diff(array_keys($variables), $this->variableNames);

        if (count($unknown)) {
            throw new InvalidArgumentException(sprintf('Unknown variables: "%s".', implode('", "', $unknown)));
        }

        $this->variables = $variables;
    }

    /**
     * Gets the expression.
     *
     * @return string The expression
     */
    public function __toString()
    {
        return (string) $this->expression;
    }
}
