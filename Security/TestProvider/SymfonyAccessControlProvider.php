<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestProvider;

use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Routing\Route;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Expression;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Yarhon\RouteGuardBundle\Security\Http\RequestConstraint;
use Yarhon\RouteGuardBundle\Security\Http\RouteMatcher;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Test\SymfonyAccessControlTest;
use Yarhon\RouteGuardBundle\Security\Http\RequestDependentTestBag;
use Yarhon\RouteGuardBundle\Security\Authorization\ExpressionVoter;
use Yarhon\RouteGuardBundle\Exception\LogicException;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

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
     * @var RouteMatcher
     */
    private $routeMatcher;

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var array
     */
    private $tests = [];

    /**
     * @param RouteMatcher $routeMatcher
     */
    public function __construct(RouteMatcher $routeMatcher)
    {
        $this->routeMatcher = $routeMatcher;
    }

    /**
     * @param ExpressionLanguage $expressionLanguage
     */
    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * @param array $rules
     */
    public function importRules(array $rules)
    {
        foreach ($rules as $rule) {
            $transformed = $this->transformRule($rule);
            $this->addRule(...$transformed);
        }
    }

    /**
     * @param RequestConstraint        $constraint
     * @param SymfonyAccessControlTest $test
     */
    public function addRule(RequestConstraint $constraint, SymfonyAccessControlTest $test)
    {
        $this->rules[] = [$constraint, $test];
    }

    /**
     * @param array $rule
     *
     * @return array
     */
    private function transformRule(array $rule)
    {
        $constraint = new RequestConstraint($rule['path'], $rule['host'], $rule['methods'], $rule['ips']);

        $attributes = $rule['roles'];
        if ($rule['allow_if']) {
            if (!$this->expressionLanguage) {
                throw new LogicException('Cannot create expression because ExpressionLanguage is not provided.');
            }

            $expression = $this->createExpression($rule['allow_if']);
            $attributes[] = $expression;
        }

        $uniqueKey = $this->getTestAttributesUniqueKey($attributes);

        if (!isset($this->tests[$uniqueKey])) {
            $this->tests[$uniqueKey] = new SymfonyAccessControlTest($attributes);
        }

        $test = $this->tests[$uniqueKey];

        return [$constraint, $test];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests($routeName, Route $route, ControllerMetadata $controllerMetadata = null)
    {
        $matches = [];

        foreach ($this->rules as $index => list($constraint, $test)) {
            $matchResult = $this->routeMatcher->matches($route, $constraint);

            if (false === $matchResult) {
                continue;
            }

            $tests = [$test];

            if (true === $matchResult) {
                $matches[$index] = [$tests, null];
                break;
            }

            if ($matchResult instanceof RequestConstraint) {
                $matches[$index] = [$tests, $matchResult];
                continue;
            }
        }

        if (!count($matches)) {
            return null;
        }

        $originalMatches = $matches;
        $matches = array_values($matches);

        if (1 === count($matches) && null === $matches[0][1]) {
            // Always matching rule was found, and there were no possibly matching rules found before,
            // so we don't need a RequestDependentTestBag for resolving it by RequestContext in runtime.
            $testBag = new TestBag($matches[0][0]);
        } else {
            $this->logRuntimeMatching($route, $routeName, $originalMatches);
            $testBag = new RequestDependentTestBag($matches);
        }

        return $testBag;
    }

    /**
     * @param string $expression
     *
     * @return Expression
     *
     * @throws InvalidArgumentException
     */
    private function createExpression($expression)
    {
        $names = ExpressionVoter::getVariableNames();

        try {
            $parsed = $this->expressionLanguage->parse($expression, $names);
        } catch (SyntaxError $e) {
            throw new InvalidArgumentException(sprintf('Cannot parse expression "%s" with following variables: "%s".', $expression, implode('", "', $names)), 0, $e);
        }

        return $parsed;
    }

    /**
     * @param Route  $route
     * @param string $routeName
     * @param array  $matches
     */
    private function logRuntimeMatching(Route $route, $routeName, array $matches)
    {
        if (!$this->logger) {
            return;
        }

        $message = 'Route "%s" (path "%s") requires runtime matching to access_control rule(s) #%s (zero-based), this would reduce performance.';
        $this->logger->warning(sprintf($message, $routeName, $route->getPath(), implode(', #', array_keys($matches))));
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    private function getTestAttributesUniqueKey(array $attributes)
    {
        $roles = $attributes;

        $expressions = array_filter($attributes, function ($attribute) {
            return $attribute instanceof Expression;
        });

        $roles = array_diff($roles, $expressions);

        $expressions = array_map(function ($expression) {
            return (string) $expression;
        }, $expressions);

        $roles = array_unique($roles);
        sort($roles);

        return implode('#', array_merge($roles, $expressions));
    }
}
