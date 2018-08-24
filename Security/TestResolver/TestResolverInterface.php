<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface TestResolverInterface
{
    /**
     * @param TestBagInterface $testBag
     *
     * @return ???
     */
    public function resolve(TestBagInterface $testBag);

    /**
     * @return string
     */
    public function getName();
}
