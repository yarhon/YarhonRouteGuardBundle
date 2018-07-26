<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestMatcher
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

    public function matches(Request $request)
    {
        if ($this->constraint->getMethods() && !in_array($request->getMethod(), $this->constraint->getMethods(), true)) {
            return false;
        }

        if (null !== $this->constraint->getPathPattern() && !preg_match('{'.$this->constraint->getPathPattern().'}', rawurldecode($request->getPathInfo()))) {
            return false;
        }

        if (null !== $this->constraint->getHostPattern() && !preg_match('{'.$this->constraint->getHostPattern().'}i', $request->getHost())) {
            return false;
        }

        if ($this->constraint->getIps() && !IpUtils::checkIp($request->getClientIp(), $this->constraint->getIps())) {
            return false;
        }

        return true;
    }
}
