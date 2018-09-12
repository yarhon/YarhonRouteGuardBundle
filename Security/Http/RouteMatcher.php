<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Symfony\Component\Routing\Route;

/**
 * RouteMatcher checks if Route would always/possibly/never match a RequestConstraint.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMatcher
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * RouteMatcher constructor.
     *
     * @param RequestConstraint $constraint
     */
    public function __construct(RequestConstraint $constraint)
    {
        // Note: order of parameters should be equal to the order of RequestConstraint constructor arguments.
        $this->parameters = [
            'pathPattern' => $constraint->getPathPattern(),
            'hostPattern' => $constraint->getHostPattern(),
            'methods' => $constraint->getMethods(),
            'ips' => $constraint->getIps(),
        ];
    }

    /**
     * @param Route $route
     *
     * @return bool|RequestConstraint Boolean true/false if route would always/never match RequestConstraint
     *                                A fresh RequestConstraint instance if route would possibly match RequestConstraint
     */
    public function matches(Route $route)
    {
        // All parameters equal to false (like nulls, empty strings and arrays) would be filtered out.
        $parameters = array_filter($this->parameters);

        if (0 === count($parameters)) {
            // If all parameters are empty, route would always match
            return true;
        }

        $matchResults = [];

        foreach ($parameters as $parameter => $value) {
            $matcher = 'match'.ucfirst($parameter);
            $matchResults[$parameter] = method_exists($this, $matcher) ? $this->$matcher($route, $value) : 0;
        }

        if (in_array(-1, $matchResults, true)) {
            // One of the parameters would never match
            return false;
        }

        if ([1] === array_unique($matchResults)) {
            // All parameters would always match
            return true;
        }

        $parameters = $this->parameters;

        // Set always matching parameters to null to avoid theirs further unnecessary matching.
        foreach (array_keys($matchResults, 1, true) as $parameter) {
            $parameters[$parameter] = null;
        }

        return new RequestConstraint(...array_values($parameters));
    }

    /**
     * Note: It's important to use the same regexp delimiters ("{}") that are used in \Symfony\Component\HttpFoundation\RequestMatcher::matches.
     *
     * Path pattern example: ^/secure1
     * Route path example: /secure1/{page}
     * Route static prefix example: /secure1
     * Route regexp example: #^/secure1/(?P<page>\d+)$#sD
     */
    private function matchPathPattern(Route $route, $pattern)
    {
        $path = $route->getPath();
        $compiledRoute = $route->compile();
        $staticPrefix = $compiledRoute->getStaticPrefix();
        $regex = $compiledRoute->getRegex();

        /// !!!!!! Symfony\Component\Routing\Matcher\UrlMatcher::137
        /// if ('' !== $compiledRoute->getStaticPrefix() && 0 !== strpos($pathinfo, $compiledRoute->getStaticPrefix()))

        // TODO: Look into case, when rule pattern has trailing slash, because it seems static prefix
        // is without trailing slash, i.e. for route "/secure1/{page}" static prefix is "/secure1",
        // but for route /secure1/ static prefix is /secure1/

        if ('^' != $pattern[0]) {

        }

        if (!preg_match('{'.$pattern.'}', $staticPrefix)) {
            return -1;
        }

        if (!$compiledRoute->getPathVariables()) {
            // route is static, so would always match
            return 1;
        }

        // Rule is one of the possible matches

        /*
        if ('$' == $pattern[strlen($pattern) - 1]) {
            if ($isRouteStatic) {
                // do something

                // This rule is the only one possible match
                return 0;
            } else {
                // This rule doesn't matches, because route has variables, prepended to static prefix,
                // but pattern requires path to end at static prefix.
                return -1;
            }
        }
        */
    }

    private function matchHostPattern(Route $route, $pattern)
    {
        return 0;
    }

    private static function determineStaticPrefix(Route $route, array $tokens): string
    {



        if ('text' !== $tokens[0][0]) {
            return ('/' !== $tokens[0][1] && false === $route->hasDefault($tokens[0][3])) ? $tokens[0][1] : '' ;
        }

        $prefix = $tokens[0][1];

        if (isset($tokens[1][1]) && '/' !== $tokens[1][1] && false === $route->hasDefault($tokens[1][3])) {
            $prefix .= $tokens[1][1];
        }

        return $prefix;
    }
}
