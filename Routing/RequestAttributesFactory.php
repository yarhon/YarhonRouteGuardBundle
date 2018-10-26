<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\RequestContext;
use Yarhon\RouteGuardBundle\Cache\CacheFactory;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestAttributesFactory implements RequestAttributesFactoryInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $routeMetadataCache;

    /**
     * @var RequestContext
     */
    private $generatorContext;

    /**
     * @var array
     */
    private $internalCache = [];

    /**
     * RequestAttributesFactory constructor.
     *
     * @param CacheItemPoolInterface $routeMetadataCache
     * @param UrlGeneratorInterface  $urlGenerator
     */
    public function __construct(CacheItemPoolInterface $routeMetadataCache, UrlGeneratorInterface $urlGenerator)
    {
        $this->routeMetadataCache = $routeMetadataCache;
        $this->generatorContext = $urlGenerator->getContext();
    }

    /**
     * @see \Symfony\Component\Routing\Matcher\UrlMatcher::getAttributes
     * @see \Symfony\Component\HttpKernel\EventListener\RouterListener::onKernelRequest
     *
     * {@inheritdoc}
     */
    public function createAttributes(RouteContextInterface $routeContext)
    {
        $cacheKey = spl_object_hash($routeContext);

        if (isset($this->internalCache[$cacheKey])) {
            return $this->internalCache[$cacheKey];
        }

        $routeMetadata = $this->getRouteMetadata($routeContext->getName());

        $parameters = $routeContext->getParameters();

        $defaults = $routeMetadata->getDefaults();

        // Special default parameters returned (if present): _format, _fragment, _locale

        // See \Symfony\Component\Routing\Matcher\UrlMatcher::mergeDefaults
        foreach ($parameters as $key => $value) {
            if (is_int($key) || null === $value) {
                unset($parameters[$key]);
            }
        }

        $variables = array_flip($routeMetadata->getVariables());

        $parameters = array_replace($this->generatorContext->getParameters(), $parameters);

        // We should add only parameters being used as route variables, others wouldn't be presented in generated url,
        // and therefore wouldn't be returned by the UrlMatcher as request attributes.
        $parameters = array_intersect_key($parameters, $variables);

        $attributes = array_replace($defaults, $parameters);

        if ($diff = array_diff_key($variables, $attributes)) {
            $missing = implode('", "', array_keys($diff));
            // TODO: don't throw this exception, because url generator would do this instead?
            throw new RuntimeException(sprintf('Some mandatory parameters are missing ("%s") to get attributes for route.', $missing));
        }

        return $this->internalCache[$cacheKey] = new ParameterBag($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeNames(RouteMetadata $routeMetadata)
    {
        $names = array_merge($routeMetadata->getVariables(), array_keys($routeMetadata->getDefaults()));
        $names = array_unique($names);

        return $names;
    }

    /**
     * @param string $routeName
     *
     * @return RouteMetadata
     *
     * @throws RuntimeException
     */
    private function getRouteMetadata($routeName)
    {
        $cacheKey = CacheFactory::getValidCacheKey($routeName);
        $cacheItem = $this->routeMetadataCache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            throw new RuntimeException(sprintf('Cannot get RouteMetadata for route "%s".', $routeName));
        }

        return $cacheItem->get();
    }
}