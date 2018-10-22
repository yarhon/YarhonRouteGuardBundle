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
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Yarhon\RouteGuardBundle\Security\Http\RequestConstraint;
use Yarhon\RouteGuardBundle\Security\Http\RouteMatcher;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMap;
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
class SymfonyAccessControlProvider implements TestProviderInterface
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
    private $testArguments = [];

    /**
     * SymfonyAccessControlProvider constructor.
     *
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
     * @param RequestConstraint $constraint
     * @param TestArguments     $arguments
     */
    public function addRule(RequestConstraint $constraint, TestArguments $arguments)
    {
        $this->rules[] = [$constraint, $arguments];
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

        $arguments = $this->createTestArguments($attributes);

        return [$constraint, $arguments];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(Route $route, $routeName, $controllerName = null)
    {
        $matches = [];

        foreach ($this->rules as $index => $rule) {
            list($constraint, $arguments) = $rule;

            $matchResult = $this->routeMatcher->matches($route, $constraint);

            if (false === $matchResult) {
                continue;
            }

            $testBag = new TestBag([$arguments]);

            if (true === $matchResult) {
                $matches[$index] = [$testBag, null];
                break;
            }

            if ($matchResult instanceof RequestConstraint) {
                $matches[$index] = [$testBag, $matchResult];
                continue;
            }
        }

        if (!count($matches)) {
            return null;
        }

        if (1 === count($matches) && null === current($matches)[1]) {
            // Always matching rule was found, and there were no possibly matching rules found before,
            // so we don't need a TestBagMap for resolving it by RequestContext in runtime.
            $testBag = current($matches)[0];
        } else {
            $this->logRuntimeMatching($route, $matches);
            $testBag = new TestBagMap(array_values($matches));
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
     * @param Route $route
     * @param array $matches
     */
    private function logRuntimeMatching(Route $route, array $matches)
    {
        if (!$this->logger) {
            return;
        }

        $message = 'Route with path "%s" requires runtime matching to access_control rule(s) #%s (zero-based), this would reduce performance.';
        $this->logger->warning(sprintf($message, $route->getPath(), implode(', #', array_keys($matches))));
    }

    /**
     * @param array $attributes
     *
     * @return TestArguments
     */
    private function createTestArguments(array $attributes)
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

        $uniqueKey = implode('#', array_merge($roles, $expressions));

        if (!isset($this->testArguments[$uniqueKey])) {
            $this->testArguments[$uniqueKey] = new TestArguments($attributes);
        }

        return $this->testArguments[$uniqueKey];
    }
}
