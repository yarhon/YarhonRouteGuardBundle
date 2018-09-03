<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Twig;

use Twig\Extension\RuntimeExtensionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\AuthorizationManager;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RoutingRuntime implements RuntimeExtensionInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var AuthorizationManager
     */
    protected $authorizationManager;

    public function __construct(UrlGeneratorInterface $urlGenerator, RouterInterface $router, AuthorizationManager $authorizationManager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->routes = $router->getRouteCollection();
        $this->authorizationManager = $authorizationManager;
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
    protected function detectLocalizedRoute($name, array $parameters = [])
    {
        $defaultLocale = null;
        $contextLocale = $this->urlGenerator->getContext()->getParameter('_locale');

        $locale = isset($parameters['_locale']) ? $parameters['_locale'] : $contextLocale ?: $defaultLocale;

        if (null !== $locale) {
            $localizedName = $name.'.'.$locale;
            if (null !== ($route = $this->routes->get($localizedName)) && $route->getDefault('_canonical_route') === $name) {
                return $localizedName;
            }
        }
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param int    $referenceType One of UrlGeneratorInterface constants
     *
     * @return string|bool
     */
    protected function generate($name, array $parameters = [], $method = 'GET', $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $localizedName = $this->detectLocalizedRoute($name, $parameters);

        $routeContext = new RouteContext($localizedName ?: $name, $parameters, $method, $referenceType);

        $isGranted = $this->authorizationManager->isGranted($routeContext);

        if (!$isGranted) {
            return false;
        }

        if (($urlDeferred = $routeContext->getUrlDeferred()) && $generatedUrl = $urlDeferred->getGeneratedUrl()) {
            return $generatedUrl;
        }

        return $this->urlGenerator->generate($name, $parameters, $referenceType);
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param array  $generateAs
     *
     * @return string|bool
     */
    public function route($name, array $parameters = [], $method = 'GET', array $generateAs = [])
    {
        $generateAsDefault = ['path', false];
        $generateAs += $generateAsDefault;

        $referenceType = null;

        if ($generateAs[0] === 'path') {
            $referenceType = $generateAs[1] ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH;
        } elseif ($generateAs[0] === 'url') {
            $referenceType = $generateAs[1] ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL;
        } else {
            throw new InvalidArgumentException(sprintf('Invalid reference type: "%s"', $generateAs));
        }

        return $this->generate($name, $parameters, $method, $referenceType);
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param bool   $relative
     *
     * @return string|bool
     */
    public function path($name, array $parameters = [], $method = 'GET', $relative = false)
    {
        $referenceType = $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH;

        return $this->generate($name, $parameters, $method, $referenceType);
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param bool   $relative
     *
     * @return string|bool
     */
    public function url($name, array $parameters = [], $method = 'GET', $relative = false)
    {
        $referenceType = $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL;

        return $this->generate($name, $parameters, $method, $referenceType);
    }
}
