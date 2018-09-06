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
interface AbstractTestBagInterface extends \IteratorAggregate, \Countable
{
    /**
     * @param string $name
    */
    public function setProviderName($name);

    /**
     * @return string
     */
    public function getProviderName();

    /**
     * @param mixed $metadata
     */
    public function setMetadata($metadata);

    /**
     * @return mixed
     */
    public function getMetadata();
}
