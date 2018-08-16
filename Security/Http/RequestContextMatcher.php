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
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestContextMatcher
{
    /**
     * @var RequestConstraint
     */
    private $constraint;

    /**
     * RequestMatcher constructor.
     *
     * @param RequestConstraint $constraint
     */
    public function __construct(RequestConstraint $constraint)
    {
        $this->constraint = $constraint;
    }

    public function matches(RequestContext $requestContext)
    {
        if ($this->constraint->getMethods() && !in_array($requestContext->getMethod(), $this->constraint->getMethods(), true)) {
            return false;
        }

        if (null !== $this->constraint->getPathPattern() && !preg_match('{'.$this->constraint->getPathPattern().'}', rawurldecode($requestContext->getPathInfo()))) {
            return false;
        }

        if (null !== $this->constraint->getHostPattern() && !preg_match('{'.$this->constraint->getHostPattern().'}i', $requestContext->getHost())) {
            return false;
        }

        if ($this->constraint->getIps() && !IpUtils::checkIp($requestContext->getClientIp(), $this->constraint->getIps())) {
            return false;
        }

        return true;
    }
}
