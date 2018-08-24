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
interface UrlPrototypeInterface
{
    /**
     * @param string $name
     * @param array  $parameters
     * @param int    $referenceType
     */
    public function __construct($name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @return int
     */
    public function getReferenceType();
}
