<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\RequestContext;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestAttributesFactory implements RequestAttributesFactoryInterface
{
    /**
     * @var RouteMetadataFactory
     */
    private $routeMetadataFactory;

    /**
     * @var RequestContext
     */
    private $generatorContext;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * RequestAttributesFactory constructor.
     *
     * @param RouteMetadataFactory  $routeMetadataFactory
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(RouteMetadataFactory $routeMetadataFactory, UrlGeneratorInterface $urlGenerator)
    {
        $this->routeMetadataFactory = $routeMetadataFactory;
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

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $routeMetadata = $this->routeMetadataFactory->createMetadata($routeContext->getName());

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

        return $this->cache[$cacheKey] = new ParameterBag($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeNames($routeName)
    {
        $routeMetadata = $this->routeMetadataFactory->createMetadata($routeName);

        $names = array_merge($routeMetadata->getVariables(), array_keys($routeMetadata->getDefaults()));
        $names = array_unique($names);

        return $names;
    }
}
