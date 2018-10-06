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
     * @var string
     */
    private $defaultHost;

    /**
     * @var RegexParser
     */
    private $regexParser;

    /**
     * RouteMatcher constructor.
     *
     * @param string|null      $defaultHost
     * @param RegexParser|null $regexParser
     */
    public function __construct($defaultHost = null, RegexParser $regexParser = null)
    {
        $this->defaultHost = $defaultHost;
        $this->regexParser = $regexParser ?: new RegexParser();
    }


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

    private function matchPathPattern(Route $route, $pattern)
    {
        $compiledRoute = $route->compile();
        $staticPrefix = $compiledRoute->getStaticPrefix();

        // If route is static (no path variables), static prefix would be equal to the resulting url for the route.
        if (!$compiledRoute->getPathVariables()) {
            // Note: It's important to use the same regexp delimiters ("{}") used in \Symfony\Component\HttpFoundation\RequestMatcher::matches.
            return (preg_match('{'.$pattern.'}', $staticPrefix)) ? 1 : -1;
        }

        if ('' === $staticPrefix) {
            return 0;
        }

        return $this->matchRegexStaticPrefix($staticPrefix, $pattern);
    }

    private function matchHostPattern(Route $route, $pattern)
    {
        $compiledRoute = $route->compile();

        $host = $route->getHost() ?: $this->defaultHost;

        if (!$host) {
            return 0;
        }

        if (!$compiledRoute->getHostVariables()) {
            // Note: It's important to use the same regexp delimiters ("{}") used in \Symfony\Component\HttpFoundation\RequestMatcher::matches.
            return (preg_match('{'.$pattern.'}i', $host)) ? 1 : -1;
        }

        $staticPrefix = strstr($host, '{', true);

        if ('' === $staticPrefix) {
            return 0;
        }

        return $this->matchRegexStaticPrefix($staticPrefix, $pattern);
    }

    private function matchRegexStaticPrefix($staticPrefix, $pattern)
    {
        $parsedPattern = $this->regexParser->parse($pattern);

        if (!$parsedPattern['hasStringStartAssert']) {
            return 0;
        }

        $patternStaticPrefix = $parsedPattern['staticPrefix'];
        $compareLength = min(strlen($staticPrefix), strlen($patternStaticPrefix));

        return (strncmp($staticPrefix, $patternStaticPrefix, $compareLength) === 0) ? 0 : -1;
    }

    private function matchMethods(Route $route, array $methods)
    {
        if (!$route->getMethods()) {
            return 0;
        }

        $matchingMethods = array_intersect($route->getMethods(), $methods);

        if (!$matchingMethods) {
            return -1;
        }

        if ($matchingMethods == $route->getMethods()) {
            return 1;
        }

        return 0;
    }


    private function determineOptionalSeparator(Route $route)
    {
        $compiledRoute = $route->compile();

        $tokens = array_reverse($compiledRoute->getTokens());

        // We need first token of "variable" type
        $token = ('text' !== $tokens[0][0]) ? $tokens[0] : (isset($tokens[1])) ? $tokens[1] : null;

        // Static prefix would optionally contain terminating separator depending on first variable used in route path:
        // - variable has default value - no separator
        // - variable doesn't have default value, but is separated from preceding text by "/" separator - no separator
        // - in all other cases terminating separator would be present in static prefix.
        //
        // Depending on $parameters argument, passed to the UrlGenerator::generate, this optional separator would be
        // present or not in the resulting url.

        $optionalSeparator = ($token && ($route->hasDefault($token[3]) || '/' === $token[1])) ? $token[1] : null;

        // return $optionalSeparator;


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

}
