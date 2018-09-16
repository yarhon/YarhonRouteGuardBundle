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
use Yarhon\RouteGuardBundle\Exception\ExceptionInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface AccessMapInterface
{
    /**
     * @param string                     $routeName
     * @param AbstractTestBagInterface[] $testBags
     */
    public function add($routeName, array $testBags);

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
     * @param string                  $routeName
     * @param ExceptionInterface|null $exception
     */
    public function addException($routeName, ExceptionInterface $exception = null);

    /**
     * @param string $routeName
     *
     * @return ExceptionInterface|null
     */
    public function getException($routeName);

    /**
     * @param string $routeName
     *
     * @return bool
     */
    public function hasException($routeName);

    public function clear();
}
