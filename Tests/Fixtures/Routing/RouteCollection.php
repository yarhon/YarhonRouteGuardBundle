<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Fixtures\Routing;

use Symfony\Component\Routing\RouteCollection as BaseRouteCollection;
use Symfony\Component\Routing\Route;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteCollection extends BaseRouteCollection
{
    public function __construct()
    {
        $routes = [
           'name1' => new Route('path1', [], []),
       ];

        foreach ($routes as $name => $route) {
            $this->add($name, $route);
        }
    }
}
