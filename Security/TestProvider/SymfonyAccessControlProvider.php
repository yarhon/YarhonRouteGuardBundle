<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestProvider;

use Symfony\Component\Routing\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Psr\Log\LoggerAwareTrait;
use Yarhon\RouteGuardBundle\Security\Http\RequestConstraint;
use Yarhon\RouteGuardBundle\Security\Http\RouteMatcher;
use Yarhon\RouteGuardBundle\Security\Http\RequestContextMatcher;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMap;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionFactoryInterface;

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
     * @var array
     */
    private $rules = [];

    /**
     * @var ExpressionFactoryInterface
     */
    private $expressionFactory;

    /**
     * SymfonyAccessControlProvider constructor.
     *
     * @param ExpressionFactoryInterface $expressionFactory
     */
    public function __construct(ExpressionFactoryInterface $expressionFactory)
    {
        $this->expressionFactory = $expressionFactory;
    }

    public function importRules(array $rules)
    {
        foreach ($rules as $rule) {
            $transformed = $this->transformRule($rule);
            $this->addRule(...$transformed);
        }
    }

    public function addRule(RequestConstraint $constraint, TestArguments $arguments)
    {
        $routeMatcher = new RouteMatcher($constraint);

        $this->rules[] = [$routeMatcher, $arguments, $constraint];
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
            $expression = $this->expressionFactory->create($rule['allow_if'], ['request']);
            $attributes[] = $expression;
        }

        $arguments = new TestArguments($attributes);
        $arguments->setSubjectMetadata(TestArguments::SUBJECT_CONTEXT_VARIABLE, 'request');

        return [$constraint, $arguments];
    }

    public function onBuild()
    {
        $this->inspectRules();
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
                $matches[] = [$testBag, $matchResult];
                continue;
            }
        }

        if (1 == count($matches) && null === $matches[0][1]) {
            // Always matching rule was found, and there were no possibly matching rules found before,
            // so we don't need a TestBagMap for resolving it by RequestContext in runtime.
            return $matches[0][0];
        } elseif (count($matches)) {
            return new TestBagMap($matches);
        }

        return null;
    }

    private function inspectRules()
    {
        if (!$this->logger) {
            return;
        }

        foreach ($this->rules as $index => $rule) {
            /** @var RequestConstraint $constraint */
            $constraint = $rule[2];

            if (!$pathPatten = $constraint->getPathPattern()) {
                continue;
            }

            if ('^' !== $pathPatten[0]) {
                $message = 'Access control rule #%s path pattern "%s" doesn\'t starts from "^" - that makes matching pattern to route static prefix impossible and reduces performance.';
                $this->logger->warning(sprintf($message, $index, $pathPatten));
            }
        }
    }
}
