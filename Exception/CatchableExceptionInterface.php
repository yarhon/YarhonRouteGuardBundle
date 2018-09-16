<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Exception;

/**
 * Interface for exceptions that could be caught and saved / logged instead of direct throwing.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface CatchableExceptionInterface extends ExceptionInterface
{
}
