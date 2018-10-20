<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface RequestAttributesFactoryInterface
{
    /**
     * @param RouteContextInterface $routeContext
     *
     * @return ParameterBag
     *
     * @throws RuntimeException
     */
    public function createAttributes(RouteContextInterface $routeContext);
}
