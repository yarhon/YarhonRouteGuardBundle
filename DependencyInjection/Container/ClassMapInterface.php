<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection\Container;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ClassMapInterface
{
    /**
     * @param string $id
     *
     * @return bool
     *
     * @throws \LogicException
     */
    public function has($id);

    /**
     * @param string $id
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function get($id);
}
