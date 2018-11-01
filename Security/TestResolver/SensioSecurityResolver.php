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
use Yarhon\RouteGuardBundle\Security\Test\IsGrantedTest;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactoryInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Security\TestProvider\SensioSecurityProvider;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * Sensio FrameworkExtraBundle allows to use Request attributes, in addition to the controller arguments, as variables
 * in "@Security" annotation expressions and in "@IsGranted" annotation "subject" arguments.
 * SensioSecurityResolver allows to fallback to the Request attribute, if controller doesn't have requested argument.
 *
 * @see \Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter::getControllerArguments
 *
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
        if (!($testBag instanceof TestBagInterface)) {
            // TODO: throw exception
        }

        $tests = $testBag->getTests();

        foreach ($tests as $test) {
            $this->resolveVariables($test, $routeContext);
        }

        return $tests;
    }

    /**
     * @param IsGrantedTest         $test
     * @param RouteContextInterface $routeContext
     */
    private function resolveVariables(IsGrantedTest $test, RouteContextInterface $routeContext)
    {
        $requestAttributes = $test->getMetadata('request_attributes') ?: [];

        if ($subjectName = $test->getMetadata('subject_name')) {
            $variableDescription = sprintf('subject variable "%s"', $subjectName);
            $value = $this->resolveVariable($routeContext, $subjectName, $requestAttributes, $variableDescription);
            $test->setSubject($value);
        }

        foreach ($test->getAttributes() as $attribute) {
            if ($attribute instanceof ExpressionDecorator) {
                $values = [];
                foreach ($attribute->getVariableNames() as $name) {
                    $variableDescription = sprintf('expression variable "%s" of expression "%s"', $name, (string) $attribute->getExpression());
                    $value = $this->resolveVariable($routeContext, $name, $requestAttributes, $variableDescription);
                    $values[$name] = $value;
                }
                $attribute->setVariables($values);
            }
        }
    }

    private function resolveVariable(RouteContextInterface $routeContext, $name, $requestAttributes, $variableDescription)
    {
        if (in_array($name, $requestAttributes, true)) {
            $requestAttributes = $this->requestAttributesFactory->createAttributes($routeContext);
            if (!$requestAttributes->has($name)) {
                $message = sprintf('Cannot resolve %s directly from Request attributes.', $variableDescription);
                throw new RuntimeException(sprintf($message, $routeContext->getName(), $name));
            }

            return $requestAttributes->get($name);
        }

        try {
            $value = $this->controllerArgumentResolver->getArgument($routeContext, $name);
        } catch (RuntimeException $e) {
            $message = sprintf('Cannot resolve %s. %s', $variableDescription, $e->getMessage());
            throw new RuntimeException($message, 0, $e);
        }

        return $value;
    }
}
