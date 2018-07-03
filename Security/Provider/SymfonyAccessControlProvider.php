<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Provider;

use Symfony\Component\Routing\Route;
use Yarhon\LinkGuardBundle\Security\Authorization\ArgumentBag;
use Yarhon\LinkGuardBundle\Security\Http\RequestMatcher;

/**
 * SymfonyAccessControlProvider processes access_control config of Symfony SecurityBundle.
 *
 * @see https://symfony.com/doc/4.1/security/access_control.html
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlProvider implements ProviderInterface
{
    /**
     * @var array
     */
    private $rules = [];

    /**
     * SymfonyAccessControlProvider constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param array $rule
     *
     * @throws \InvalidArgumentException (see self::normalizeRule method)
     */
    public function addRule(array $rule)
    {
        $rule = $this->normalizeRule($rule);

        $this->rules[] = [
            'pattern' => $rule['path'],
            'host' => $rule['host'],
            'ips' => $rule['ips'],
            'roles' => $rule['roles'],
            'expression' => $rule['allow_if'],

            'methods' => array_map('strtoupper', $rule['methods']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteRules(Route $route)
    {
        /*
        Rule pattern example: ^/secure1

        Route path example: /secure1/{page}
        Static prefix example: /secure1
        Regexp example: #^/secure1/(?P<page>\d+)$#sD
         */

        $compiledRoute = $route->compile();

        $path = $route->getPath();
        $staticPrefix = $compiledRoute->getStaticPrefix();
        $regex = $compiledRoute->getRegex();

        // or use $compiledRoute->getPathVariables() - if count is 0 - means static (check _locale in this case)
        $isRouteStatic = $path === $staticPrefix;

        foreach ($this->rules as $rule) {
            $pattern = $rule['pattern'];

            /* TODO: Look into case, when rule pattern has trailing slash, because it seems static prefix
            is without trailing slash, i.e. for route "/secure1/{page}" static prefix is "/secure1"
            */

            if ('^' != $pattern[0]) {
                // TODO: issue some warning in debug, because in this case we can't rely on static prefix
            }

            // Note: the delimiter in pattern should be the same as used in \Symfony\Component\HttpFoundation\RequestMatcher::matches
            if (!preg_match('{'.$pattern.'}', $staticPrefix)) {
                continue;
            }

            // Rule is one of the possible matches

            if ('$' == $pattern[strlen($pattern) - 1]) {
                if ($isRouteStatic) {
                    // do something

                    // This rule is the only one possible match
                    break;
                } else {
                    // This rule doesn't matches, because route has variables, prepended to static prefix,
                    // but pattern requires path to end at static prefix.
                    continue;
                }
            }

            $argumentBag = $this->createArgumentBag($rule['roles'], $rule['expression']);

            $requestMatcher = new RequestMatcher($pattern, $rule['host'], $rule['ips']);
        }

        // var_dump($path, $staticPrefix, $regex, '-----------');

        return [];
    }

    private function createArgumentBag(array $roles, $expression = null)
    {
        $argumentBag = new ArgumentBag();
        $argumentBag->setAttributes($roles);

        if ($expression) {
            $argumentBag->addAttribute($expression);
        }

        $argumentBag->setSubjectMetadata(ArgumentBag::SUBJECT_CONTEXT_VARIABLE, 'request');

        return $argumentBag;
    }

    /**
     * Checks and normalizes config rule, according to configuration defined in
     * \Symfony\Bundle\SecurityBundle\DependencyInjection\MainConfiguration::addAccessControlSection.
     *
     * @param array $rule
     *
     * @return array $rule
     *
     * @throws \InvalidArgumentException When rule array keys doesn't correspond to configuration prototype
     *                                   (possibly, BC breaking changes in Symfony SecurityBundle)
     */
    private function normalizeRule(array $rule)
    {
        $prototype = [
            'path' => null,
            'host' => null,
            'methods' => [],
            'ips' => [],
            'roles' => [],
            'allow_if' => null,
            'requires_channel' => null,
        ];

        $diff = array_diff(array_keys($rule), array_keys($prototype));

        if (count($diff)) {
            throw new \InvalidArgumentException(sprintf('Options %s are not supported in access_control rules.',
                implode(', ', $diff)));
        }

        $rule = array_merge($prototype, $rule);

        if (is_string($rule['methods'])) {
            $rule['methods'] = preg_split('/\s*,\s*/', $rule['methods']);
        }

        if (is_string($rule['roles'])) {
            $rule['roles'] = preg_split('/\s*,\s*/', $rule['roles']);
        }

        if (is_string($rule['ips'])) {
            $rule['ips'] = [$rule['ips']];
        }

        return $rule;
    }
}
