<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\Sensio\VariableResolver;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\Sensio\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Security\TestProvider\SensioSecurityProvider;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityResolver implements TestResolverInterface
{
    /**
     * @var VariableResolver
     */
    private $variableResolver;

    /**
     * SensioSecurityResolver constructor.
     *
     * @param VariableResolver $variableResolver
     */
    public function __construct(VariableResolver $variableResolver)
    {
        $this->variableResolver = $variableResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderClass()
    {
        return SensioSecurityProvider::class;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(AbstractTestBagInterface $testBag, RouteContextInterface $routeContext)
    {
        $this->resolveVariables($testBag, $routeContext);

        $tests = [];

        foreach ($testBag as $testArguments) {
            $tests[] = $testArguments;
        }

        return $tests;
    }

    /**
     * @param TestBagInterface      $testBag
     * @param RouteContextInterface $routeContext
     */
    private function resolveVariables(TestBagInterface $testBag, RouteContextInterface $routeContext)
    {
        $resolved = [];

        $resolve = function ($name) use ($routeContext, &$resolved) {
            if (!array_key_exists($name, $resolved)) {
                $resolved[$name] = $this->variableResolver->getVariable($routeContext, $name);
            }

            return $resolved[$name];
        };

        foreach ($testBag as $testArguments) {
            /** @var TestArguments $testArguments */
            if ($subjectName = $testArguments->getMetadata()) {
                try {
                    $value = $resolve($subjectName);
                } catch (RuntimeException $e) {
                    $message = sprintf('Cannot resolve subject variable "%s". %s', $subjectName, $e->getMessage());
                    throw new RuntimeException($message, 0, $e);
                }
                $testArguments->setSubject($value);
            }

            foreach ($testArguments->getAttributes() as $attribute) {
                if ($attribute instanceof ExpressionDecorator) {
                    $values = [];
                    foreach ($attribute->getNames() as $name) {
                        try {
                            $values[$name] = $resolve($name);
                        } catch (RuntimeException $e) {
                            $message = sprintf('Cannot resolve expression variable "%s" of expression "%s". %s', $name, (string) $attribute->getExpression(), $e->getMessage());
                            throw new RuntimeException($message, 0, $e);
                        }
                    }
                    $attribute->setVariables($values);
                }
            }
        }
    }
}
