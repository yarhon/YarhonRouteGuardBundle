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

    /**
     * @param Route  $route
     * @param string $pattern
     *
     * @return int
     */
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

        return $this->matchStaticPrefixToPattern($staticPrefix, $pattern);
    }

    /**
     * @param Route  $route
     * @param string $pattern
     *
     * @return int
     */
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

        return $this->matchStaticPrefixToPattern($staticPrefix, $pattern, false);
    }

    /**
     * @param Route $route
     * @param array $methods
     *
     * @return int
     */
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

    /**
     * @param string $staticPrefix
     * @param string $pattern
     * @param bool   $caseSensitive
     *
     * @return int
     */
    private function matchStaticPrefixToPattern($staticPrefix, $pattern, $caseSensitive = true)
    {
        $parsedPattern = $this->regexParser->parse($pattern);
        $patternStaticPrefix = $parsedPattern['staticPrefix'];

        if ('' === $patternStaticPrefix) {
            return 0;
        }

        if ($parsedPattern['hasStringStartAssert']) {
            $compareLength = min(strlen($staticPrefix), strlen($patternStaticPrefix));
            $compareFunction = $caseSensitive ? 'strncmp' : 'strncasecmp';
            $compareResult = $compareFunction($staticPrefix, $patternStaticPrefix, $compareLength);

            if (0 !== $compareResult) {
                return -1;
            }

            if ($parsedPattern['dynamicPartIsWildcard'] && strlen($patternStaticPrefix) <= strlen($staticPrefix)) {
                return 1;
            }
        } else {
            $searchFunction = $caseSensitive ? 'strpos' : 'stripos';
            if ($parsedPattern['dynamicPartIsWildcard'] && false !== $searchFunction($staticPrefix, $patternStaticPrefix)) {
                return 1;
            }
        }

        return 0;
    }
}
