<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Http;

use Symfony\Component\Routing\Route;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * This class checks if route would match a request constraint.
 */
class RouteRequestConstraintMatcher
{
    /**
     * Means that route always matches RequestConstraint
     */
    const MATCH_ALWAYS = 1;

    /**
     * Means that route can match RequestConstraint, depending on path and/or runtime parameters.
     */
    const MATCH_POSSIBLE = 2;

    /**
     * Means that route would never match RequestConstraint
     */
    const MATCH_NEVER = 3;

    /**
     * @var Route
     */
    private $route;

    /**
     * RouteRequestConstraintMatcher
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Note: It's important to use the same regexp delimiters ("{}") as are used in \Symfony\Component\HttpFoundation\RequestMatcher::matches
     *
     * @param RequestConstraint $constraint
     *
     * @return int One of self::MATCH_* constants
     */
    public function matches(RequestConstraint $constraint)
    {
        /*
        Constraint path pattern example: ^/secure1
        Route path example: /secure1/{page}
        Route static prefix example: /secure1
        Route regexp example: #^/secure1/(?P<page>\d+)$#sD
        */

        $path = $this->route->getPath();
        $compiledRoute = $this->route->compile();
        $staticPrefix = $compiledRoute->getStaticPrefix();
        $regex = $compiledRoute->getRegex();

        // or use $compiledRoute->getPathVariables() - if count is 0 - means static (check _locale in this case)
        $isRouteStatic = $path === $staticPrefix;

        /////////////////////////////////////

        $pattern = $constraint->getPathPattern();

        /* TODO: Look into case, when rule pattern has trailing slash, because it seems static prefix
        is without trailing slash, i.e. for route "/secure1/{page}" static prefix is "/secure1"
        */

        if ('^' != $pattern[0]) {
            // TODO: issue some warning in debug, because in this case we can't rely on static prefix
        }

        if (!preg_match('{'.$pattern.'}', $staticPrefix)) {
            return self::MATCH_NEVER;
        }

        // Rule is one of the possible matches

        if ('$' == $pattern[strlen($pattern) - 1]) {
            if ($isRouteStatic) {
                // do something

                // This rule is the only one possible match
                return self::MATCH_POSSIBLE;
            } else {
                // This rule doesn't matches, because route has variables, prepended to static prefix,
                // but pattern requires path to end at static prefix.
                return self::MATCH_NEVER;
            }
        }
    }
}
