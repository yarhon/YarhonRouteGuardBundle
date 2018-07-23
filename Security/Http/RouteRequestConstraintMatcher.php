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
 * RouteRequestConstraintMatcher checks at compile time if route would always/possibly/never match a RequestConstraint at runtime.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteRequestConstraintMatcher
{
    /**
     * Means that route always matches RequestConstraint.
     */
    const MATCH_ALWAYS = 1;

    /**
     * Means that route can match RequestConstraint, depending on runtime parameters.
     */
    const MATCH_POSSIBLE = 2;

    /**
     * Means that route would never match RequestConstraint.
     */
    const MATCH_NEVER = 3;

    /**
     * @var Route
     */
    private $route;

    /**
     * RouteRequestConstraintMatcher constructor.
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Note: It's important to use the same regexp delimiters ("{}") as are used in \Symfony\Component\HttpFoundation\RequestMatcher::matches.
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

        var_dump($compiledRoute->getPathVariables());

        // or use $compiledRoute->getPathVariables() - if count is 0 - means static (check _locale in this case)
        $isRouteStatic = $path === $staticPrefix;

        /// !!!!!!
        /// \Symfony\Component\Routing\Matcher\UrlMatcher::137
        /// if ('' !== $compiledRoute->getStaticPrefix() && 0 !== strpos($pathinfo, $compiledRoute->getStaticPrefix()))

        /////////////////////////////////////

        $pattern = $constraint->getPathPattern();

        /* TODO: Look into case, when rule pattern has trailing slash, because it seems static prefix
        is without trailing slash, i.e. for route "/secure1/{page}" static prefix is "/secure1",
        but for route /secure1/ static prefix is /secure1/
        */

        if ('^' != $pattern[0]) {
            // TODO: issue some warning in debug, because in this case we can't rely on static prefix
        }

        if (!preg_match('{'.$pattern.'}', $staticPrefix)) {
            return self::MATCH_NEVER;
        }

        if (!$compiledRoute->getPathVariables()) {
            // route is static, so would always match

            // !!! consider other parameters - host, methods, ips

            return self::MATCH_ALWAYS;
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
