<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
trait HelperTrait
{
    private function requiresClass($class)
    {
        if (!class_exists($class, false)) {
            $this->markTestSkipped(sprintf('Requires %s class.', $class));
        }
    }
}
