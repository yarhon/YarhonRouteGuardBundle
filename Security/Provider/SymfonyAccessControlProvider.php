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
use Symfony\Component\ExpressionLanguage\Expression;
use Psr\Log\LoggerAwareTrait;
use Yarhon\LinkGuardBundle\Security\Http\RequestConstraint;
use Yarhon\LinkGuardBundle\Security\Http\RouteMatcher;
use Yarhon\LinkGuardBundle\Security\Http\RequestMatcher;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\Arguments;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\TestBag;
use Yarhon\LinkGuardBundle\Security\Http\TestBagMap;

/**
 * SymfonyAccessControlProvider processes access_control config of Symfony SecurityBundle.
 *
 * @see https://symfony.com/doc/4.1/security/access_control.html
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlProvider implements ProviderInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @param array $rule
     */
    public function addRule(array $rule)
    {
        $this->inspectRule($rule, count($this->rules));

        $constraint = new RequestConstraint($rule['path'], $rule['host'], $rule['methods'], $rule['ips']);
        $routeMatcher = new RouteMatcher($constraint);

        $attributes = $rule['roles'];
        if ($rule['allow_if'] && class_exists(Expression::class)) {
            // When allow_if is specified, but ExpressionLanguage component is not installed,
            // Symfony SecurityBundle would throw an exception, so we don't have to duplicate it.
            $expression = new Expression($rule['allow_if']);
            $attributes[] = $expression;
        }

        $arguments = new Arguments($attributes);
        $arguments->setSubjectMetadata(Arguments::SUBJECT_CONTEXT_VARIABLE, 'request');

        $this->rules[] = [$routeMatcher, $arguments];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(Route $route)
    {
        $matches = [];

        foreach ($this->rules as $rule) {
            /** @var RouteMatcher $routeMatcher */
            list($routeMatcher, $arguments) = $rule;

            $matchResult = $routeMatcher->matches($route);

            if (false === $matchResult) {
                continue;
            }

            // TODO: create TestBag in the addRule method? (make sure that one TestBag instance can be shared across different routes)
            $testBag = new TestBag([$arguments]);

            if (true == $matchResult) {
                $matches[] = [$testBag, null];
                break;
            }

            if ($matchResult instanceof RequestConstraint) {
                $matches[] = [$testBag, new RequestMatcher($matchResult)];
                continue;
            }
        }

        if (1 == count($matches) && null === $matches[0][1]) {
            // Always matching rule was found, and there were no possibly matching rules found before,
            // so we don't need a TestBagMap for resolving it by Request in runtime.
            return $matches[0][0];
        } elseif (count($matches)) {
            return new TestBagMap($matches);
        }

        return null;
    }

    private function inspectRule(array $rule, $index)
    {
        if (!$this->logger) {
            return;
        }

        if ($rule['path'] && '^' !== $rule['path'][0]) {
            $message = 'Access control rule #%s path pattern "%s" doesn\'t starts from "^" - that makes matching pattern to route static prefix impossible and reduces performance.';
            $message = sprintf($message, $index, $rule['path']);
            $this->logger->warning($message, $rule);
        }
    }
}