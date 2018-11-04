<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Test;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ProviderAwareInterface
{
    /**
     * @param string $class
     */
    public function setProviderClass($class);

    /**
     * @return string
     */
    public function getProviderClass();
}
