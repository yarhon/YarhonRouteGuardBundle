<?php
/*
*
* (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Yarhon\RouteGuardBundle\Security;

use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface AccessMapInterface
{
    /**
     * @param string                     $routeName
     * @param AbstractTestBagInterface[] $testBags
     *
     * @return bool
     */
    public function set($routeName, array $testBags);

    /**
     * @param string $routeName
     *
     * @return AbstractTestBagInterface[]|null
     */
    public function get($routeName);

    /**
     * @param string $routeName
     *
     * @return bool
     */
    public function has($routeName);

    /**
     * @return bool
     */
    public function clear();
}
