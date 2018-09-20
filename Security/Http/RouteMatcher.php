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
     * @param Route             $route
     * @param RequestConstraint $constraint
     *
     * @return bool|RequestConstraint Boolean true/false if route would always/never match RequestConstraint
     *                                A fresh RequestConstraint instance if route would possibly match RequestConstraint
     */
    public function matches(Route $route, RequestConstraint $constraint)
    {
        // Note: order of parameters should be equal to the order of RequestConstraint constructor arguments.
        $originalParameters = [
            'pathPattern' => $constraint->getPathPattern(),
            'hostPattern' => $constraint->getHostPattern(),
            'methods' => $constraint->getMethods(),
            'ips' => $constraint->getIps(),
        ];

        // All parameters equal to false (like nulls, empty strings and arrays) would be filtered out.
        $parameters = array_filter($originalParameters);

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

        if ([1] === array_unique(array_values($matchResults))) {
            // All parameters would always match
            return true;
        }

        $parameters = $originalParameters;

        // Set always matching parameters to null to avoid theirs further unnecessary matching.
        foreach (array_keys($matchResults, 1, true) as $parameter) {
            $parameters[$parameter] = null;
        }

        return new RequestConstraint(...array_values($parameters));
    }

    /**
     *
     *
     * Path pattern example: ^/secure1
     * Route path example: /secure1/{page}
     * Route static prefix example: /secure1
     * Route regexp example: #^/secure1/(?P<page>\d+)$#sD
     */
    private function matchPathPattern(Route $route, $pattern)
    {
        $compiledRoute = $route->compile();
        $staticPrefix = $compiledRoute->getStaticPrefix();

        if ('' === $staticPrefix) {
            return 0;
        }

        // Note: It's important to use the same regexp delimiters ("{}") used in \Symfony\Component\HttpFoundation\RequestMatcher::matches.
        $staticPrefixMatchesPattern = preg_match('{'.$pattern.'}', $staticPrefix);

        // If route is static (no path variables), static prefix would be equal to the resulting url for the route.
        if (!$compiledRoute->getPathVariables()) {
            return $staticPrefixMatchesPattern ? 1 : -1;
        }

        // If pattern has "string start" assert, and static prefix doesn't matches pattern, resulting url would never match pattern.
        if ('^' === $pattern[0] && !$staticPrefixMatchesPattern) {
            return -1;
        }

        $lastIndex = strlen($pattern) - 1;
        $hasStringEndAssert = ('$' === $pattern[$lastIndex] && (!isset($pattern[$lastIndex - 1]) || '\\' !== $pattern[$lastIndex - 1]));

        // If pattern doesn't have "string end" assert and static prefix matches pattern, resulting url would always match pattern.
        if (!$hasStringEndAssert && $staticPrefixMatchesPattern) {
            return 1;
        }

        return 0;
    }

    private function matchStaticPrefix(Route $route, $pattern)
    {
        $compiledRoute = $route->compile();
        $staticPrefix = $compiledRoute->getStaticPrefix();

        // Note: It's important to use the same regexp delimiters ("{}") used in \Symfony\Component\HttpFoundation\RequestMatcher::matches.
        if (preg_match('{'.$pattern.'}', $staticPrefix)) {
            return 1;
        }

        $tokens = array_reverse($compiledRoute->getTokens());
        $token = ('text' !== $tokens[0][0]) ? $tokens[0] : (isset($tokens[1])) ? $tokens[1] : null;

        $optionalSeparator = ($token && ($route->hasDefault($token[3]) || '/' === $token[1])) ? $token[1] : null;

        if ($optionalSeparator && preg_match('{'.$pattern.'}', $staticPrefix.$optionalSeparator)) {
            return 0;
        }

        //return -1;

        ///////////////////////////////////////

        if ('text' !== $tokens[0][0]) {
            // when

            return ($route->hasDefault($tokens[0][3]) || '/' === $tokens[0][1]) ? '' : $tokens[0][1];
        }

        $prefix = $tokens[0][1];

        if (isset($tokens[1][1]) && '/' !== $tokens[1][1] && false === $route->hasDefault($tokens[1][3])) {
            $prefix .= $tokens[1][1];
        }

        return $prefix;
    }

    private function matchHostPattern(Route $route, $pattern)
    {
        $compiledRoute = $route->compile();

        if (!$compiledRoute->getHostVariables()) {
            //return $staticPrefixMatchesPattern ? 1 : -1;
        }


    }


}
