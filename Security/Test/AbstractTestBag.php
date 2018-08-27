<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Test;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
abstract class AbstractTestBag implements AbstractTestBagInterface
{
    protected $elements;

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    public function count()
    {
        return count($this->elements);
    }
}
