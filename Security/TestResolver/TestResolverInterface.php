<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface TestResolverInterface
{
    /**
     * @param AbstractTestBagInterface $testBag
     * @param RouteContextInterface    $routeContext
     *
     * @return TestInterface[]
     *
     * @throws RuntimeException
     */
    public function resolve(AbstractTestBagInterface $testBag, RouteContextInterface $routeContext);

    /**
     * @return string
     */
    public function getProviderClass();
}
