<?php
/*
*
* (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Yarhon\RouteGuardBundle\Security;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMap
{
    private $map = [];

    public function add($routeName, $providerName, $testBag)
    {
        $routePointer = &$this->map[$routeName];
        $routePointer[$providerName] = $testBag;
    }

    public function get($routeName)
    {
        return isset($this->map[$routeName]) ? $this->map[$routeName] : null;
    }
}
