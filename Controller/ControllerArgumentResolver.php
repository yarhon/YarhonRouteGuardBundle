<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactoryInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentValueResolverInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * Responsible for resolving the argument passed to an action.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerArgumentResolver implements ControllerArgumentResolverInterface
{
    /**
     * @var ControllerMetadataFactory
     */
    private $controllerMetadataFactory;

    /**
     * @var RequestAttributesFactoryInterface
     */
    private $requestAttributesFactory;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var \Traversable|ArgumentValueResolverInterface[]
     */
    private $argumentValueResolvers;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * ControllerArgumentResolver constructor.
     *
     * @param ControllerMetadataFactory                     $controllerMetadataFactory
     * @param RequestAttributesFactoryInterface             $requestAttributesFactory
     * @param RequestStack                                  $requestStack
     * @param \Traversable|ArgumentValueResolverInterface[] $argumentValueResolvers
     */
    public function __construct(ControllerMetadataFactory $controllerMetadataFactory, RequestAttributesFactoryInterface $requestAttributesFactory, RequestStack $requestStack, $argumentValueResolvers = [])
    {
        $this->controllerMetadataFactory = $controllerMetadataFactory;
        $this->requestAttributesFactory = $requestAttributesFactory;
        $this->requestStack = $requestStack;
        $this->argumentValueResolvers = $argumentValueResolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function getArgument(RouteContextInterface $routeContext, $name)
    {
        $cacheKey = spl_object_hash($routeContext).'#'.$name;

        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        $controllerMetadata = $this->controllerMetadataFactory->createMetadata($routeContext->getName());

        if (null === $controllerMetadata) {
            $message = 'Route "%s" does not have controller or controller name is unresolvable.';
            throw new RuntimeException(sprintf($message, $routeContext->getName()));
        }

        if (!$controllerMetadata->has($name)) {
            $message = 'Route "%s" controller "%s" does not have argument "$%s".';
            throw new RuntimeException(sprintf($message, $routeContext->getName(), $controllerMetadata->getName(), $name));
        }

        $requestAttributes = $this->requestAttributesFactory->createAttributes($routeContext);

        $resolverContext = new ArgumentResolverContext($requestAttributes, $controllerMetadata->getName(), $this->requestStack->getCurrentRequest());

        $argumentMetadata = $controllerMetadata->get($name);

        foreach ($this->argumentValueResolvers as $resolver) {
            if (!$resolver->supports($resolverContext, $argumentMetadata)) {
                continue;
            }

            return $this->cache[$cacheKey] = $resolver->resolve($resolverContext, $argumentMetadata);
        }

        $message = 'Route "%s" controller "%s" requires that you provide a value for the "$%s" argument.';
        throw new RuntimeException(sprintf($message, $routeContext->getName(), $controllerMetadata->getName(), $name));
    }

    /**
     * {@inheritdoc}
     */
    public function getArgumentNames($routeName)
    {
        $controllerMetadata = $this->controllerMetadataFactory->createMetadata($routeName);

        if (null === $controllerMetadata) {
            return [];
        }

        return $controllerMetadata->keys();
    }
}
