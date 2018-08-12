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

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ExpressionFactoryInterface
{
    /**
     * @param string $expression
     * @param array  $names
     *
     * @return Expression
     */
    public function create($expression, array $names = []);
}
