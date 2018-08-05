<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Controller;

use Yarhon\LinkGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ControllerNameResolverInterface
{
    /**
     * @param mixed $controller
     *
     * @return string|false A controller name in the class::method notation, or false if controller name is unavailable
     *
     * @throws InvalidArgumentException If failed to resolve controller name when controller is not callable
     *                                  If failed to resolve controller class
     */
    public function resolve($controller);
}
