<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface AuthorizedUrlGeneratorInterface
{
    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param int    $referenceType One of UrlGeneratorInterface constants
     *
     * @return string|bool
     */
    public function generate($name, $parameters = [], $method = 'GET', $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH);
}
