<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface SymfonySecurityResolverInterface
{
    /**
     * @param TestInterface $test
     *
     * @return bool
     */
    public function supports(TestInterface $test);

    /**
     * @param TestInterface         $test
     * @param RouteContextInterface $routeContext
     *
     * @return array An array of arguments to pass to the test call
     *
     * @throws RuntimeException
     */
    public function resolve(TestInterface $test, RouteContextInterface $routeContext);
}
