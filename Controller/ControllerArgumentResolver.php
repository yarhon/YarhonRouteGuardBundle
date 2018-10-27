<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Yarhon\RouteGuardBundle\Cache\CacheFactory;
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
     * @var CacheItemPoolInterface
     */
    private $controllerMetadataCache;

    /**
     * @var RequestAttributesFactoryInterface
     */
    protected $requestAttributesFactory;

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
    private $internalCache = [];

    /**
     * ControllerArgumentResolver constructor.
     *
     * @param CacheItemPoolInterface                        $controllerMetadataCache
     * @param RequestAttributesFactoryInterface             $requestAttributesFactory
     * @param RequestStack                                  $requestStack
     * @param \Traversable|ArgumentValueResolverInterface[] $argumentValueResolvers
     */
    public function __construct(CacheItemPoolInterface $controllerMetadataCache, RequestAttributesFactoryInterface $requestAttributesFactory, RequestStack $requestStack, $argumentValueResolvers = [])
    {
        $this->controllerMetadataCache = $controllerMetadataCache;
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

        if (array_key_exists($cacheKey, $this->internalCache)) {
            return $this->internalCache[$cacheKey];
        }

        $controllerMetadata = $this->getControllerMetadata($routeContext->getName());

        if (null === $controllerMetadata) {
            $message = 'Route "%s" does not have controller or controller name is unresolvable.';
            throw new RuntimeException(sprintf($message, $routeContext->getName()));
        }

        if (!$controllerMetadata->hasArgument($name)) {
            $message = 'Route "%s" controller "%s" does not have argument "$%s".';
            throw new RuntimeException(sprintf($message, $routeContext->getName(), $controllerMetadata->getName(), $name));
        }

        $requestAttributes = $this->requestAttributesFactory->createAttributes($routeContext);

        $resolverContext = new ArgumentResolverContext($requestAttributes, $controllerMetadata->getName(), $this->requestStack->getCurrentRequest());

        $argumentMetadata = $controllerMetadata->getArgument($name);

        foreach ($this->argumentValueResolvers as $resolver) {
            if (!$resolver->supports($resolverContext, $argumentMetadata)) {
                continue;
            }

            return $this->internalCache[$cacheKey] = $resolver->resolve($resolverContext, $argumentMetadata);
        }

        $message = 'Route "%s" controller "%s" requires that you provide a value for the "$%s" argument.';
        throw new RuntimeException(sprintf($message, $routeContext->getName(), $controllerMetadata->getName(), $name));
    }

    /**
     * @param string $routeName
     *
     * @return ControllerMetadata
     *
     * @throws RuntimeException
     */
    protected function getControllerMetadata($routeName)
    {
        $cacheKey = CacheFactory::getValidCacheKey($routeName);
        $cacheItem = $this->controllerMetadataCache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            throw new RuntimeException(sprintf('Cannot get ControllerMetadata for route "%s".', $routeName));
        }

        return $cacheItem->get();
    }
}
