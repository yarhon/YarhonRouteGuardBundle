<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ControllerNameResolverInterface
{
    /**
     * @param mixed $controller
     *
     * @return string|null A controller name in the class::method notation, or null if controller name is unavailable
     *
     * @throws InvalidArgumentException If failed to resolve controller name when controller is not callable
     *                                  If failed to resolve controller class
     */
    public function resolve($controller);
}
