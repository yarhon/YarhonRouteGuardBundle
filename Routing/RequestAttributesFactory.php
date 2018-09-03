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
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestAttributesFactory
{
    /**
     * @var array
     */
    private $contextParameters;

    /**
     * RequestAttributesFactory constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->contextParameters = $urlGenerator->getContext()->getParameters();
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

        /*
        if (isset($defaults['_canonical_route'])) {
            $name = $defaults['_canonical_route'];
        }
        $parameters['_route'] = $name;
        */

        unset($defaults['_canonical_route'], $defaults['_controller']);
        // Other special parameters returned (if present): _format, _fragment, _locale

        // See \Symfony\Component\Routing\Matcher\UrlMatcher::mergeDefaults
        foreach ($parameters as $key => $value) {
            if (is_int($key) || null === $value) {
                unset($parameters[$key]);
            }
        }

        $variables = array_flip($routeMetadata->getVariables());

        // We should add only context parameters being used as route variables, others wouldn't be presented in generated url,
        // and therefore wouldn't be returned by the UrlMatcher.
        $contextParameters = array_intersect_key($this->contextParameters, $variables);

        $attributes = array_replace($defaults, $contextParameters, $parameters);

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
     * @return array
     */
    public function getAttributeNames(RouteMetadataInterface $routeMetadata)
    {
        $defaults = $routeMetadata->getDefaults();
        unset($defaults['_canonical_route'], $defaults['_controller']);
        //$defaults = array_keys()

    }
}
