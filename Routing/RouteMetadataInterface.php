<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface RouteMetadataInterface
{
    /**
     * @return string
     */
    public function getControllerName();

    /**
     * @return array
     */
    public function getDefaults();

    /**
     * @return array
     */
    public function getVariables();

}
