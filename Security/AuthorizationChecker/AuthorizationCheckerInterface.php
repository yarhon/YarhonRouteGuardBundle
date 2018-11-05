<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\AuthorizationChecker;

use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface AuthorizationCheckerInterface
{
    /**
     * @param TestInterface         $test
     * @param RouteContextInterface $routeContext
     *
     * @return bool
     */
    public function isGranted(TestInterface $test, RouteContextInterface $routeContext);

    /**
     * @param TestInterface $test
     *
     * @return bool
     */
    public function supports(TestInterface $test);
}
