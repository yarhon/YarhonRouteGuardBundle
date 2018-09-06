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
class SensioSecurityExpression
{
    /**
     * @var Expression;
     */
    private $expression;

    /**
     * @var array
     */
    private $names;

    /**
     * @var array
     */
    private $variables = [];

    /**
     * SensioSecurityExpression constructor.
     *
     * @param Expression $expression
     * @param array      $names
     */
    public function __construct(Expression $expression, array $names = [])
    {
        $this->expression = $expression;
        $this->names = $names;
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
    public function getNames()
    {
        return $this->names;
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
        $missed = array_diff($this->names, array_keys($variables));

        if (count($missed)) {
            throw new InvalidArgumentException(sprintf('Missed variables: %s', implode(', ', $missed)));
        }

        $unknown = array_diff(array_keys($variables), $this->names);

        if (count($unknown)) {
            throw new InvalidArgumentException(sprintf('Unknown variables: %s', implode(', ', $unknown)));
        }

        $this->variables = $variables;
    }
}
