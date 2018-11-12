<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Security\Test\SensioExtraTest;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactoryInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * Sensio FrameworkExtraBundle allows to use Request attributes, in addition to the controller arguments, as variables
 * in "@Security" annotation expressions and in "@IsGranted" annotation "subject" arguments.
 * SensioExtraResolver allows to fallback to the Request attribute, if controller doesn't have requested argument.
 *
 * @see \Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter::getControllerArguments
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioExtraResolver implements SymfonySecurityResolverInterface
{
    /**
     * @var ArgumentResolverInterface
     */
    private $controllerArgumentResolver;

    /**
     * @var RequestAttributesFactoryInterface
     */
    private $requestAttributesFactory;

    /**
     * @param ArgumentResolverInterface $controllerArgumentResolver
     * @param RequestAttributesFactoryInterface   $requestAttributesFactory
     */
    public function __construct(ArgumentResolverInterface $controllerArgumentResolver, RequestAttributesFactoryInterface $requestAttributesFactory)
    {
        $this->controllerArgumentResolver = $controllerArgumentResolver;
        $this->requestAttributesFactory = $requestAttributesFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TestInterface $test)
    {
        return $test instanceof SensioExtraTest;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(TestInterface $test, RouteContextInterface $routeContext)
    {
        /* @var SensioExtraTest $test */

        $requestAttributes = $test->getMetadata('request_attributes') ?: [];

        $attributes = $test->getAttributes();
        $subject = $test->getSubject();

        if ($subject) {
            $variableDescription = sprintf('subject variable "%s"', $subject);
            $subject = $this->resolveVariable($routeContext, $subject, $requestAttributes, $variableDescription);
        }

        foreach ($attributes as $attribute) {
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

        return [$attributes, $subject];
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
