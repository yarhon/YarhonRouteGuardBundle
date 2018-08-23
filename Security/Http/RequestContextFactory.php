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
use Yarhon\RouteGuardBundle\Routing\UrlDeferredInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestContextFactory
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * TestBagMapResolver constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param RequestStack          $requestStack
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, RequestStack $requestStack)
    {
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    /**
     * @param UrlDeferredInterface $urlDeferred
     * @param string               $method
     *
     * @return RequestContext
     */
    public function create(UrlDeferredInterface $urlDeferred, $method)
    {
        $urlGenerator = $this->urlGenerator;

        $pathInfoClosure = function() use ($urlDeferred, $urlGenerator) {
            return $urlDeferred->generate($urlGenerator)->getPathInfo();
        };

        // TODO: set host as string to $requestContext if possible (route has no host, or route has static host)
        $hostClosure = function() use ($urlDeferred, $urlGenerator) {
            return $urlDeferred->generate($urlGenerator)->getHost();
        };

        $request = $this->requestStack->getCurrentRequest();

        $requestContext = new RequestContext($pathInfoClosure, $hostClosure, $method, $request->getClientIp());

        return $requestContext;
    }
}
