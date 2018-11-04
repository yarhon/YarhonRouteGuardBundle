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
trait ProviderAwareTrait
{
    /**
     * @var string
     */
    protected $providerClass;

    /**
     * @param string $class
     */
    public function setProviderClass($class)
    {
        $this->providerClass = $class;
    }

    /**
     * @return string
     */
    public function getProviderClass()
    {
        return $this->providerClass;
    }
}
