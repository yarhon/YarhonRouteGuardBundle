<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Controller;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ControllerNameDeprecationsConverterInterface
{
    /**
     * @param string $controller
     *
     * @return string A converted controller in the class::method notation
     */
    public function convert($controller);
}
