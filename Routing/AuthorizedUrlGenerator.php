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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Yarhon\RouteGuardBundle\Security\RouteAuthorizationCheckerInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AuthorizedUrlGenerator implements AuthorizedUrlGeneratorInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $delegate;

    /**
     * @var RouteAuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var RouteCollection
     */
    protected $routes;

    public function __construct(UrlGeneratorInterface $urlGenerator, RouteAuthorizationCheckerInterface $authorizationChecker, RouterInterface $router)
    {
        $this->delegate = $urlGenerator;
        $this->authorizationChecker = $authorizationChecker;
        $this->routes = $router->getRouteCollection();
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param int    $referenceType One of UrlGeneratorInterface constants
     *
     * @return string|bool
     */
    public function generate($name, $parameters = [], $method = 'GET', $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $localizedName = $this->detectLocalizedRoute($name, $parameters);

        if ($localizedName) {
            unset($parameters['_locale']);
        }

        $routeContext = new RouteContext($localizedName ?: $name, $parameters, $method, $referenceType);

        $isGranted = $this->authorizationChecker->isGranted($routeContext);

        if (!$isGranted) {
            return false;
        }

        if ($generatedUrl = $routeContext->getGeneratedUrl()) {
            return $generatedUrl;
        }

        return $this->delegate->generate($name, $parameters, $referenceType);
    }

    /**
     * @see \Symfony\Component\Routing\Generator\UrlGenerator::generate
     * Note: UrlGenerator uses $defaultLocale parameter when determining locale for url generation, but
     * a) it's never passed to UrlGenerator constructor - see \Symfony\Component\Routing\Router::getGenerator
     * b) we have no way to retrieve it
     *
     * @param $name
     * @param array $parameters
     *
     * @return string|null
     */
    protected function detectLocalizedRoute($name, array $parameters)
    {
        $defaultLocale = null;
        $contextLocale = $this->delegate->getContext()->getParameter('_locale');

        $locale = isset($parameters['_locale']) ? $parameters['_locale'] : $contextLocale ?: $defaultLocale;

        if (null !== $locale) {
            $localizedName = $name.'.'.$locale;
            if (null !== ($route = $this->routes->get($localizedName)) && $route->getDefault('_canonical_route') === $name) {
                return $localizedName;
            }
        }
    }
}
