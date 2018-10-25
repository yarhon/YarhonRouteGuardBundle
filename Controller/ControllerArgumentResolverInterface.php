<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ControllerArgumentResolverInterface
{
    /**
     * @param RouteContextInterface $routeContext
     * @param string                $name
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function getArgument(RouteContextInterface $routeContext, $name);
}
