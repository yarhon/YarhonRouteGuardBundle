<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Routing\RouteCollection;

use Symfony\Component\Routing\RouteCollection;

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
     * @throws \InvalidArgumentException
     */
    public function transform(RouteCollection $routeCollection);
}
