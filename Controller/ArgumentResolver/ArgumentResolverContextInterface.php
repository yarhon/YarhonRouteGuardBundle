<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ArgumentResolverContextInterface
{
    /**
     * @return Request|null
     */
    public function getRequest();

    /**
     * @return ParameterBag
     */
    public function getAttributes();

    /**
     * @return string
     */
    public function getControllerName();
}
