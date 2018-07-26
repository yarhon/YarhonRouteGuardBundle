<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Http;

use Symfony\Component\HttpFoundation\RequestMatcher;

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
    private $methods = [];

    /**
     * @var array
     */
    private $ips = [];

    /**
     * RequestConstraint constructor.
     *
     * @param string|null $pathPattern
     * @param string|null $hostPattern
     * @param string[]    $methods
     * @param string[]    $ips
     */
    public function __construct($pathPattern = null, $hostPattern = null, array $methods = [], array $ips = [])
    {
        $this->pathPattern = $pathPattern;
        $this->hostPattern = $hostPattern;
        $this->methods = array_map('strtoupper', $methods);
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
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return array
     */
    public function getIps()
    {
        return $this->ips;
    }

}
