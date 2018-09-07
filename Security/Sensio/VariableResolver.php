<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Sensio;

use Symfony\Component\HttpFoundation\RequestStack;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class VariableResolver
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

    public function __construct(RequestAttributesFactory $requestAttributesFactory, ControllerArgumentResolver $controllerArgumentResolver, RequestStack $requestStack)
    {
        $this->requestAttributesFactory = $requestAttributesFactory;
        $this->controllerArgumentResolver = $controllerArgumentResolver;
        $this->requestStack = $requestStack;
    }

    /**
     * @param RouteMetadataInterface $routeMetadata
     * @param ControllerMetadata     $controllerMetadata
     * @param array                  $parameters
     *
     * @return VariableResolverContext
     */
    public function createContext(RouteMetadataInterface $routeMetadata, ControllerMetadata $controllerMetadata, array $parameters)
    {
        $requestAttributes = $this->requestAttributesFactory->getAttributes($routeMetadata, $parameters);

        $context = new VariableResolverContext($routeMetadata, $controllerMetadata, $requestAttributes);

        return $context;
    }

    /**
     * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\IsGrantedListener::onKernelControllerArguments
     * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener::onKernelControllerArguments
     *
     * @param VariableResolverContext $context
     * @param string                  $name
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function getVariable(VariableResolverContext $context, $name)
    {
        if ($context->getControllerMetadata()->has($name)) {
            $argumentMetadata = $context->getControllerMetadata()->get($name);
            $argumentResolverContext = new ArgumentResolverContext($context->getRequestAttributes(), $context->getRouteMetadata()->getControllerName(), $this->requestStack->getCurrentRequest());

            return $this->controllerArgumentResolver->getArgument($argumentResolverContext, $argumentMetadata);
        } elseif ($context->getRequestAttributes()->has($name)) {
            return $resolved[$name] = $context->getRequestAttributes()->get($name);
        }

        throw new RuntimeException(sprintf('Variable is neither a controller argument nor request attribute.'));
    }

    /**
     * @param RouteMetadataInterface $routeMetadata
     * @param ControllerMetadata     $controllerMetadata
     *
     * @return string[]
     */
    public function getVariableNames(RouteMetadataInterface $routeMetadata, ControllerMetadata $controllerMetadata)
    {
        $requestAttributes = $this->requestAttributesFactory->getAttributesPrototype($routeMetadata);
        // $argumentResolverContext = new ArgumentResolverContext($requestAttributes, $routeMetadata->getControllerName());

        $names = [];

        foreach ($controllerMetadata as $name => $argumentMetadata) {
            $isResolvable = true; // TODO: determine $isResolvable

            if ($isResolvable) {
                $names[] = $name;
            }
        }

        foreach ($requestAttributes->keys() as $name) {
            if (!$controllerMetadata->has($name)) {
                $names[] = $name;
            }
        }

        return $names;
    }
}
