<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing\RouteCollection;

use Symfony\Component\Routing\RouteCollection;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface TransformerInterface
{
    /**
     * @param RouteCollection $routeCollection
     *
     * @return RouteCollection
     *
     * @throws InvalidArgumentException
     */
    public function transform(RouteCollection $routeCollection);
}
