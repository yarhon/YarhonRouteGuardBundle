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
class RequestAttributesFactory
{
    /**
     * @var RequestContext
     */
    private $generatorContext;

    /**
     * RequestAttributesFactory constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->generatorContext = $urlGenerator->getContext();
    }

    /**
     * @see \Symfony\Component\Routing\Matcher\UrlMatcher::getAttributes
     * @see \Symfony\Component\HttpKernel\EventListener\RouterListener::onKernelRequest
     *
     * @param RouteMetadataInterface $routeMetadata
     * @param array                  $parameters
     *
     * @return ParameterBag
     *
     * @throws RuntimeException
     */
    public function getAttributes(RouteMetadataInterface $routeMetadata, array $parameters)
    {
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
        // and therefore wouldn't be returned by the UrlMatcher.
        $parameters = array_intersect_key($parameters, $variables);

        $attributes = array_replace($defaults, $parameters);

        if ($diff = array_diff_key($variables, $attributes)) {
            $missing = implode('", "', array_keys($diff));
            // TODO: don't throw this exception, because url generator would do this instead?
            throw new RuntimeException(sprintf('Some mandatory parameters are missing ("%s") to get attributes for route.', $missing));
        }

        return new ParameterBag($attributes);
    }

    /**
     * @param RouteMetadataInterface $routeMetadata
     *
     * @return ParameterBag
     */
    public function getAttributesPrototype(RouteMetadataInterface $routeMetadata)
    {
        $defaults = $routeMetadata->getDefaults();

        $attributes = array_unique(array_merge($routeMetadata->getVariables(), array_keys($defaults)));

        $attributes = array_fill_keys($attributes, null);

        return new ParameterBag($attributes);
    }
}
