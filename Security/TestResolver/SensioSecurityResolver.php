<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Symfony\Component\HttpFoundation\RequestStack;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\ExpressionLanguage\DecoratedExpression;
use Yarhon\RouteGuardBundle\Exception\LogicException;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityResolver implements TestResolverInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var RequestAttributesFactory
     */
    private $requestAttributesFactory;

    /**
     * @var ControllerArgumentResolver
     */
    private $controllerArgumentResolver;

    public function __construct(RequestStack $requestStack, RequestAttributesFactory $requestAttributesFactory, ControllerArgumentResolver $controllerArgumentResolver)
    {
        $this->requestStack = $requestStack;
        $this->requestAttributesFactory = $requestAttributesFactory;
        $this->controllerArgumentResolver = $controllerArgumentResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sensio_security';
    }

    /**
     * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\IsGrantedListener::onKernelControllerArguments
     * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener::onKernelControllerArguments
     *
     * {@inheritdoc}
     */
    public function resolve(AbstractTestBagInterface $testBag, RouteContextInterface $routeContext)
    {
        if (!($testBag instanceof TestBagInterface)) {
            throw new LogicException(sprintf('%s expects instance of %s.', __CLASS__, TestBagInterface::class));
        }

        list($routeMetadata, $controllerMetadata) = $testBag->getMetadata();

        $variableNames = $this->collectVariableNames($testBag);

        if (count($variableNames)) {

            try {
                $variables = $this->resolveVariables($routeMetadata, $controllerMetadata, $routeContext, $variableNames);
            } catch (RuntimeException $e) {
                // TODO: bypass all the following code?
                throw $e;
            }

            $this->fillVariables($testBag, $variables);
        }

        $tests = [];

        foreach ($testBag as $testArguments) {
            $tests[] = $testArguments;
        }

        return $tests;
    }

    /**
     * @param TestBagInterface $testBag
     *
     * @return array
     */
    private function collectVariableNames(TestBagInterface $testBag)
    {
        $names = [];

        foreach ($testBag as $testArguments) {
            /** @var TestArguments $testArguments */
            if ($testArguments->requiresSubject()) {
                $names[] = $testArguments->getSubjectMetadata()[0];
            }

            foreach ($testArguments->getAttributes() as $attribute) {
                if ($attribute instanceof DecoratedExpression) {
                    $names = array_merge($names, $attribute->getNames());
                }
            }
        }

        return array_unique($names);
    }

    /**
     * @param TestBagInterface $testBag
     * @param array            $variables
     */
    private function fillVariables(TestBagInterface $testBag, array $variables)
    {
        foreach ($testBag as $testArguments) {
            /** @var TestArguments $testArguments */
            if ($testArguments->requiresSubject()) {
                $name = $testArguments->getSubjectMetadata()[0];
                $testArguments->setSubject($variables[$name]);
            }

            foreach ($testArguments->getAttributes() as $attribute) {
                if ($attribute instanceof DecoratedExpression) {
                    $expressionVariables = array_intersect_key($variables, array_flip($attribute->getNames()));
                    $attribute->setVariables($expressionVariables);
                }
            }
        }
    }


    private function resolveVariables(RouteMetadata $routeMetadata, ControllerMetadata $controllerMetadata, RouteContextInterface $routeContext, array $names)
    {
        $variables = [];

        $requestAttributes = $this->requestAttributesFactory->getAttributes($routeMetadata, $routeContext->getParameters());

        $request = $this->requestStack->getCurrentRequest();
        $argumentResolverContext = new ArgumentResolverContext($request, $requestAttributes, $routeMetadata->getControllerName());

        foreach ($names as $name) {
            if ($controllerMetadata->has($name)) {
                $argumentMetadata = $controllerMetadata->get($name);
                $variables[$name] = $this->controllerArgumentResolver->getArgument($argumentResolverContext, $argumentMetadata);
            } elseif ($requestAttributes->has($name)) {
                $variables[$name] = $requestAttributes->get($name);
            } else {
                throw new RuntimeException(sprintf('Cannot resolve variable "%s" - it neither a controller argument nor route attribute.'));
            }
        }

        return $variables;
    }
}
