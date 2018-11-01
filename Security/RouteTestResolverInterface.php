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
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface RouteTestResolverInterface
{
    /**
     * @param RouteContextInterface $routeContext
     *
     * @return TestInterface[]
     */
    public function getTests(RouteContextInterface $routeContext);
}
