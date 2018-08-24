<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteAttributesFactory
{

    protected function getAttributes(Route $route, $name, array $parameters)
    {
        // See \Symfony\Component\Routing\Matcher\UrlMatcher::getAttributes
        // See \Symfony\Component\HttpKernel\EventListener\RouterListener::onKernelRequest

        $defaults = $route->getDefaults();
        if (isset($defaults['_canonical_route'])) {
            $name = $defaults['_canonical_route'];
        }
        $parameters['_route'] = $name; /// ?????????

        unset($defaults['_canonical_route'], $defaults['_controller']);

        // Other special parameters returned: _format, _fragment, _locale

        // See \Symfony\Component\Routing\Matcher\UrlMatcher::mergeDefaults
        foreach ($parameters as $key => $value) {
            if (is_int($key) || null === $value) {
                unset($parameters[$key]);
            }
        }

        $attributes = array_replace($defaults, $parameters);

        /// we should set only that variables form context, that are used as route variables path or host)
        /// $defaults = array_replace($defaults, $urlGenerator->getContext()->getParameters());

        return new ParameterBag($attributes);
    }
}
