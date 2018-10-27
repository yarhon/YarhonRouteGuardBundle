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
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactoryInterface;
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
     * @var ControllerArgumentResolverInterface
     */
    private $controllerArgumentResolver;

    /**
     * @var RequestAttributesFactoryInterface
     */
    private $requestAttributesFactory;

    /**
     * SensioSecurityResolver constructor.
     *
     * @param ControllerArgumentResolverInterface $controllerArgumentResolver
     * @param RequestAttributesFactoryInterface   $requestAttributesFactory
     */
    public function __construct(ControllerArgumentResolverInterface $controllerArgumentResolver, RequestAttributesFactoryInterface $requestAttributesFactory)
    {
        $this->controllerArgumentResolver = $controllerArgumentResolver;
        $this->requestAttributesFactory = $requestAttributesFactory;
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
        $tests = [];

        foreach ($testBag as $testArguments) {
            $this->resolveVariables($testArguments, $routeContext);
            $tests[] = $testArguments;
        }

        return $tests;
    }

    /**
     * @param TestArguments         $testArguments
     * @param RouteContextInterface $routeContext
     */
    private function resolveVariables(TestArguments $testArguments, RouteContextInterface $routeContext)
    {
        if ($subjectName = $testArguments->getMetadata('subject_name')) {
            $exceptionMessage = sprintf('subject variable "%s"', $subjectName);
            $value = $this->resolveVariable($routeContext, $subjectName, $exceptionMessage);
            $testArguments->setSubject($value);
        }

        foreach ($testArguments->getAttributes() as $attribute) {
            if ($attribute instanceof ExpressionDecorator) {
                $values = [];
                foreach ($attribute->getNames() as $name) {
                    $exceptionMessage = sprintf('expression variable "%s" of expression "%s"', $name, (string) $attribute->getExpression());
                    $value = $this->resolveVariable($routeContext, $name, $exceptionMessage);
                    $values[$name] = $value;

                }
                $attribute->setVariables($values);
            }
        }
    }

    private function resolveVariable(RouteContextInterface $routeContext, $name, $exceptionMessage)
    {
        try {
            $value = $this->controllerArgumentResolver->getArgument($routeContext, $name);
        } catch (RuntimeException $e) {
            $message = sprintf('Cannot resolve %s. %s', $exceptionMessage, $e->getMessage());
            throw new RuntimeException($message, 0, $e);
        }

        return $value;
    }
}
