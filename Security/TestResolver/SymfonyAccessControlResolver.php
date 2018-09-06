<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMapInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestContext;
use Yarhon\RouteGuardBundle\Security\Http\RouteMetadata;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlResolver implements TestResolverInterface
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'symfony_access_control';
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(AbstractTestBagInterface $testBag, RouteContextInterface $routeContext)
    {
        if (!($testBag instanceof TestBagInterface) || !($testBag instanceof TestBagMapInterface)) {
            throw new LogicException(sprintf('%s expects instance of %s or %s.', __CLASS__, TestBagInterface::class, TestBagMapInterface::class));
        }

        if ($testBag instanceof TestBagMapInterface) {
            $routeMetadata = $testBag->getMetadata();
            $requestContext = $this->createRequestContext($routeContext, $routeMetadata);
            $testBag = $testBag->resolve($requestContext);
            if (null === $testBag) {
                return [];
            }
        }

        $tests = [];

        $request = $this->requestStack->getCurrentRequest();

        foreach ($testBag as $testArguments) {
            /* @var TestArguments $testArguments */
            $testArguments->setSubject($request); // See \Symfony\Component\Security\Http\Firewall\AccessListener::handle
            $tests[] = $testArguments;
        }

        return $tests;
    }

    /**
     * @param RouteContextInterface $routeContext
     * @param RouteMetadata         $routeMetadata
     *
     * @return RequestContext
     */
    private function createRequestContext(RouteContextInterface $routeContext, RouteMetadata $routeMetadata)
    {
        $urlGenerator = $this->urlGenerator;

        $urlDeferred = $routeContext->createUrlDeferred();

        $pathInfoClosure = function () use ($urlDeferred, $urlGenerator) {
            return $urlDeferred->generate($urlGenerator)->getPathInfo();
        };

        // TODO: set host as string to $requestContext if possible (route has no host, or route has static host)
        $hostClosure = function () use ($urlDeferred, $urlGenerator) {
            return $urlDeferred->generate($urlGenerator)->getHost();
        };

        $request = $this->requestStack->getCurrentRequest();

        $requestContext = new RequestContext($pathInfoClosure, $hostClosure, $routeContext->getMethod(), $request->getClientIp());

        return $requestContext;
    }
}
