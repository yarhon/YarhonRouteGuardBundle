<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Symfony\Component\HttpFoundation\IpUtils;

/**
 * RequestConstraint holds a set of request requirements:
 * - path pattern
 * - host pattern
 * - http methods array
 * - ip addresses array.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestConstraint implements RequestConstraintInterface
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
     * {@inheritdoc}
     */
    public function getPathPattern()
    {
        return $this->pathPattern;
    }

    /**
     * {@inheritdoc}
     */
    public function getHostPattern()
    {
        return $this->hostPattern;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * {@inheritdoc}
     */
    public function getIps()
    {
        return $this->ips;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(RequestContext $requestContext)
    {
        if ($this->getMethods() && !in_array($requestContext->getMethod(), $this->getMethods(), true)) {
            return false;
        }

        if ($this->getIps() && !IpUtils::checkIp($requestContext->getClientIp(), $this->getIps())) {
            return false;
        }

        if (null !== $this->getHostPattern() && !preg_match('{'.$this->getHostPattern().'}i', $requestContext->getHost())) {
            return false;
        }

        if (null !== $this->getPathPattern() && !preg_match('{'.$this->getPathPattern().'}', rawurldecode($requestContext->getPathInfo()))) {
            return false;
        }

        return true;
    }
}
