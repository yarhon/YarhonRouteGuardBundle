<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

use Symfony\Component\Routing\Route;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadataFactory
{
    /**
     * @param Route $route
     *
     * @return RouteMetadata
     */
    public function createMetadata(Route $route)
    {
        $defaults = $route->getDefaults();
        unset($defaults['_canonical_route'], $defaults['_controller']);

        $compiledRoute = $route->compile();
        $variables = $compiledRoute->getVariables();

        return new RouteMetadata($defaults, $variables);
    }
}
