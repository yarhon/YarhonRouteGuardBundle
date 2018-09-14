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

        $urlDeferred = $routeContext->createUrlDeferred();

        $pathInfoClosure = function () use ($urlDeferred, $urlGenerator) {
            return $urlDeferred->generate($urlGenerator)->getPathInfo();
        };

        $hostClosure = function () use ($urlDeferred, $urlGenerator) {
            return $urlDeferred->generate($urlGenerator)->getHost();
        };

        $request = $this->requestStack->getCurrentRequest();

        $requestContext = new RequestContext($pathInfoClosure, $hostClosure, $routeContext->getMethod(), $request->getClientIp());

        return $requestContext;
    }
}
