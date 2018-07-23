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
 * - ip address array.
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

    public function hasRuntimeParameters()
    {
        return (bool) ($this->hostPattern || count($this->methods) || count($this->ips));
    }

    public function createRequestMatcher()
    {
        return new RequestMatcher($this->pathPattern, $this->hostPattern, $this->methods, $this->ips);
    }

    public function getPathPattern()
    {
        return $this->pathPattern;
    }
}
