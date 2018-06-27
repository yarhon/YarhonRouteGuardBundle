<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Security\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestMatcher
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
    private $ips = [];

    /**
     * RequestMatcher constructor.
     *
     * @param string      $pathPattern
     * @param string|null $hostPattern
     * @param string[]    $ips
     */
    public function __construct($pathPattern, $hostPattern = null, array $ips = [])
    {
        $this->pathPattern = $pathPattern;
        $this->hostPattern = $hostPattern;
        $this->ips = $ips;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function matches(Request $request)
    {
        if (!preg_match('{'.$this->pathPattern.'}', rawurldecode($request->getPathInfo()))) {
            return false;
        }

        if (null !== $this->hostPattern && !preg_match('{'.$this->hostPattern.'}i', $request->getHost())) {
            return false;
        }

        if (count($this->ips) && !IpUtils::checkIp($request->getClientIp(), $this->ips)) {
            return false;
        }

        return true;
    }
}
