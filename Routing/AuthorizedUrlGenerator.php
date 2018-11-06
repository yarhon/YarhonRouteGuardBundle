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
use Yarhon\RouteGuardBundle\Security\RouteAuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

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
     * @var LocalizedRouteDetector|null
     */
    protected $localizedRouteDetector;

    /**
     * @param UrlGeneratorInterface              $urlGenerator
     * @param RouteAuthorizationCheckerInterface $authorizationChecker
     * @param LocalizedRouteDetector|null        $localizedRouteDetector
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, RouteAuthorizationCheckerInterface $authorizationChecker, LocalizedRouteDetector $localizedRouteDetector = null)
    {
        $this->delegate = $urlGenerator;
        $this->authorizationChecker = $authorizationChecker;
        $this->localizedRouteDetector = $localizedRouteDetector;
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
        if (!is_string($name)) {
            throw new InvalidArgumentException(sprintf('Route name must be a string, %s given.', gettype($name)));
        }

        $originalName = $name;
        $originalParameters = $parameters;

        $localizedName = $this->localizedRouteDetector ? $this->localizedRouteDetector->getLocalizedName($name, $parameters) : null;

        if ($localizedName) {
            $name = $localizedName;
            unset($parameters['_locale']);
        }

        $routeContext = new GeneratedUrlAwareRouteContext($name, $parameters, $method);
        $routeContext->setReferenceType($referenceType);

        $isGranted = $this->authorizationChecker->isGranted($routeContext);

        if (!$isGranted) {
            return false;
        }

        if ($generatedUrl = $routeContext->getGeneratedUrl()) {
            return $generatedUrl;
        }

        return $this->delegate->generate($originalName, $originalParameters, $referenceType);
    }
}
