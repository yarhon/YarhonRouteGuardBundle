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
interface RouteContextInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return int
     */
    public function getReferenceType();

    /**
     * @return UrlDeferredInterface
     */
    public function getUrlDeferred();
}
