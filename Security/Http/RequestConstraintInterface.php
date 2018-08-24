<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface RequestConstraintInterface
{
    /**
     * @return string|null
     */
    public function getPathPattern();

    /**
     * @return string|null
     */
    public function getHostPattern();

    /**
     * @return array|null
     */
    public function getMethods();

    /**
     * @return array|null
     */
    public function getIps();
}
