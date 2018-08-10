<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

/**
 * RequestConstraint holds a set of request requirements:
 * - path pattern
 * - host pattern
 * - http methods array
 * - ip addresses array.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestConstraint
{
    /**
     * @var string
     */
    private $pathPattern;

    /**
     * @var string
     */
    private $hostPattern;

    /**
     * @var array
     */
    private $methods;

    /**
     * @var array
     */
    private $ips;

    /**
     * RequestConstraint constructor.
     *
     * @param string|null $pathPattern
     * @param string|null $hostPattern
     * @param string[]    $methods
     * @param string[]    $ips
     */
    public function __construct($pathPattern = null, $hostPattern = null, array $methods = null, array $ips = null)
    {
        if (null !== $methods) {
            $methods = array_map('strtoupper', $methods);
        }

        $this->pathPattern = $pathPattern;
        $this->hostPattern = $hostPattern;
        $this->methods = $methods;
        $this->ips = $ips;
    }

    /**
     * @return string|null
     */
    public function getPathPattern()
    {
        return $this->pathPattern;
    }

    /**
     * @return string|null
     */
    public function getHostPattern()
    {
        return $this->hostPattern;
    }

    /**
     * @return array|null
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return array|null
     */
    public function getIps()
    {
        return $this->ips;
    }
}
