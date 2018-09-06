<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface RouteAuthorizationCheckerInterface
{
    /**
     * @param RouteContextInterface $routeContext
     *
     * @return bool
     */
    public function isGranted(RouteContextInterface $routeContext);
}
