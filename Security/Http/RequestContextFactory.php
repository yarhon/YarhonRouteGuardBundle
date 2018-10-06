<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Routing\GeneratedUrlAwareInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestContextFactory
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param RouteContextInterface $routeContext
     *
     * @return RequestContext
     */
    public function createContext(RouteContextInterface $routeContext)
    {
        $urlGenerator = $this->urlGenerator;
        $urlGeneratorContext = $this->urlGenerator->getContext();

        $generateUrlClosure = function () use ($routeContext, $urlGenerator) {
            static $generated;

            if ($generated === null) {
                $referenceType = $routeContext->getReferenceType();

                // We need to parse path and host from the generated url, that depends on reference type.
                // When using ABSOLUTE_URL or NETWORK_PATH, generated url will contain both path and host.
                //
                // When using ABSOLUTE_PATH or RELATIVE_PATH, generated url will contain only path.
                // If route has some specific host assigned, the UrlGenerator will force reference type to
                // ABSOLUTE_URL or NETWORK_PATH, that would produce url with host.
                // So, with ABSOLUTE_PATH or RELATIVE_PATH, if generated url does not contains host, we can be sure
                // that the host is the "current" host, and grab it from UrlGenerator context.
                // Finally, with RELATIVE_PATH we can't simply determine path (absolute), so we force generation to
                // ABSOLUTE_URL, and don't save the generated url.

                if (UrlGeneratorInterface::RELATIVE_PATH === $referenceType) {
                    $referenceType = UrlGeneratorInterface::ABSOLUTE_URL;
                }

                $generated = $urlGenerator->generate($routeContext->getName(), $routeContext->getParameters(), $referenceType);

                if ($routeContext instanceof GeneratedUrlAwareInterface && UrlGeneratorInterface::RELATIVE_PATH !== $routeContext->getReferenceType()) {
                    $routeContext->setGeneratedUrl($generated);
                }
            }

            return $generated;
        };

        $pathInfoClosure = function () use ($generateUrlClosure, $urlGeneratorContext) {
            $url = $generateUrlClosure();
            $pathInfo = parse_url($url, PHP_URL_PATH);
            $pathInfo = substr($pathInfo, strlen($urlGeneratorContext->getBaseUrl()));

            // See \Symfony\Component\HttpFoundation\Request::preparePathInfo
            if (false === $pathInfo || '' === $pathInfo) {
                $pathInfo = '/';
            }

            return $pathInfo;
        };

        $hostClosure = function () use ($generateUrlClosure, $urlGeneratorContext) {
            $url = $generateUrlClosure();
            $host = parse_url($url, PHP_URL_HOST) ?: $urlGeneratorContext->getHost();

            return $host;
        };

        $request = $this->requestStack->getCurrentRequest();

        $requestContext = new RequestContext($pathInfoClosure, $hostClosure, $routeContext->getMethod(), $request->getClientIp());

        return $requestContext;
    }
}
