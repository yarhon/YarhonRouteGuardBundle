<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Symfony\Component\HttpFoundation\ParameterBag;
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
     * @var RequestAttributesFactory
     */
    private $requestAttributesFactory;

    /**
     * @var ControllerArgumentResolver
     */
    private $controllerArgumentResolver;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $context;

    public function __construct(RequestAttributesFactory $requestAttributesFactory, ControllerArgumentResolver $controllerArgumentResolver, RequestStack $requestStack)
    {
        $this->requestAttributesFactory = $requestAttributesFactory;
        $this->controllerArgumentResolver = $controllerArgumentResolver;
        $this->requestStack = $requestStack;
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

        $this->createContext($routeMetadata, $controllerMetadata, $routeContext->getParameters());

        $this->resolveVariables($testBag);

        $tests = [];

        foreach ($testBag as $testArguments) {
            $tests[] = $testArguments;
        }

        return $tests;
    }

    private function createContext(RouteMetadata $routeMetadata, ControllerMetadata $controllerMetadata, array $parameters)
    {
        $requestAttributes = $this->requestAttributesFactory->getAttributes($routeMetadata, $parameters);
        $argumentResolverContext = new ArgumentResolverContext($this->requestStack->getCurrentRequest(), $requestAttributes, $routeMetadata->getControllerName());

        $this->context = [
            'controllerMetadata' => $controllerMetadata,
            'requestAttributes' => $requestAttributes,
            'argumentResolverContext' => $argumentResolverContext,
            'resolved' => [],
        ];
    }

    /**
     * @param TestBagInterface $testBag
     */
    private function resolveVariables(TestBagInterface $testBag)
    {
        foreach ($testBag as $testArguments) {
            /** @var TestArguments $testArguments */
            if ($testArguments->requiresSubject()) {
                $name = $testArguments->getSubjectMetadata()[0];
                try {
                    $value = $this->resolveVariable($name);
                } catch (RuntimeException $e) {
                    // TODO: add details about variable that caused exception
                    throw $e;
                }
                $testArguments->setSubject($value);
            }

            foreach ($testArguments->getAttributes() as $attribute) {
                if ($attribute instanceof DecoratedExpression) {
                    $values = [];
                    foreach ($attribute->getNames() as $name) {
                        try {
                            $values[$name] = $this->resolveVariable($name);
                        } catch (RuntimeException $e) {
                            // TODO: add details about variable that caused exception
                            throw $e;
                        }
                    }
                    $attribute->setVariables($values);
                }
            }
        }
    }

    private function resolveVariable($name)
    {
        $resolved = &$this->context['resolved'][$name];

        if (null !== $resolved) {
            return $resolved;
        }

        $controllerMetadata = $this->context['controllerMetadata'];
        $argumentResolverContext = $this->context['argumentResolverContext'];
        $requestAttributes = $this->context['requestAttributes'];

        if ($controllerMetadata->has($name)) {
            $argumentMetadata = $controllerMetadata->get($name);

            return $resolved = $this->controllerArgumentResolver->getArgument($argumentResolverContext, $argumentMetadata);
        } elseif ($requestAttributes->has($name)) {

            return $resolved = $requestAttributes->get($name);
        }

        throw new RuntimeException(sprintf('Cannot resolve variable "%s" - it is neither a controller argument nor request attribute.', $name));
    }
}
