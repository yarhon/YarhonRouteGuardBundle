<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\DependencyInjection\Container;

use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ClassMapInterface
{
    /**
     * @param string $id
     *
     * @return bool
     */
    public function has($id);

    /**
     * @param string $id
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function get($id);
}
