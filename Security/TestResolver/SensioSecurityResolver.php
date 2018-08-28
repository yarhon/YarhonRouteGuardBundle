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
use Yarhon\RouteGuardBundle\Routing\RouteAttributesFactory;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Exception\LogicException;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

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
     * @var RouteAttributesFactory
     */
    private $routeAttributesFactory;

    /**
     * @var ControllerArgumentResolver
     */
    private $controllerArgumentResolver;

    public function __construct(RequestStack $requestStack, RouteAttributesFactory $routeAttributesFactory, ControllerArgumentResolver $controllerArgumentResolver)
    {
        $this->requestStack = $requestStack;
        $this->routeAttributesFactory = $routeAttributesFactory;
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

        $tests = [];

        $routeMetadata = null;
        /** @var ControllerMetadata $controllerMetadata */
        $controllerMetadata = null;

        try {
            $routeAttributes = $this->routeAttributesFactory->getAttributes($routeMetadata, $routeContext->getParameters());
        } catch (InvalidArgumentException $e) {
            // TODO: bypass all the following code?
            throw $e;
        }

        $request = $this->requestStack->getCurrentRequest();
        $controllerName = $controllerMetadata->getName();
        $argumentResolverContext = new ArgumentResolverContext($request, $routeAttributes, $controllerName);



        foreach ($testBag as $testArguments) {
            /** @var TestArguments $testArguments */
            if ($testArguments->requiresSubject()) {

            }
        }

        return $tests;
    }

    public function resolveArgument()
    {

    }
}
