<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class LocalizedRouteDetector
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @var RequestContext
     */
    private $context;

    /**
     * LocalizedRouteDetector constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->routeCollection = $router->getRouteCollection();
        $this->context = $router->getContext();
    }

    /**
     * @see \Symfony\Component\Routing\Generator\UrlGenerator::generate
     * Note: UrlGenerator uses $defaultLocale parameter when determining locale for url generation, but
     * a) it's never passed to UrlGenerator constructor - see \Symfony\Component\Routing\Router::getGenerator
     * b) we have no way to retrieve it
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string|null
     */
    public function getLocalizedName($name, array $parameters = [])
    {
        $defaultLocale = null;
        $contextLocale = $this->context->getParameter('_locale');

        $locale = isset($parameters['_locale']) ? $parameters['_locale'] : $contextLocale ?: $defaultLocale;

        if (null !== $locale) {
            $localizedName = $name.'.'.$locale;
            if (null !== ($route = $this->routeCollection->get($localizedName)) && $route->getDefault('_canonical_route') === $name) {
                return $localizedName;
            }
        }
    }
}
