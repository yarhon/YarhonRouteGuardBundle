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
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestContext
{
    /**
     * @var string
     */
    private $pathInfo;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $clientIp;

    /**
     * RequestContext constructor.
     *
     * @param string|null $pathInfo
     * @param string|null $host
     * @param string|null $method
     * @param string|null $clientIp
     */
    public function __construct($pathInfo = null, $host = null, $method = null, $clientIp = null)
    {
        $this->pathInfo = $pathInfo;
        $this->host = $host;
        $this->method = $method;
        $this->clientIp = $clientIp;
    }

    /**
     * @return string
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

}
