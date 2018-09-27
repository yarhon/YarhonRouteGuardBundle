<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Regex;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ParserInterface
{
    /**
     * @param string $expression
     *
     * @return ParsedExpression
     */
    public function parse($expression);
}
