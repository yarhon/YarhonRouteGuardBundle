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
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\TestProvider\SymfonyAccessControlProvider;
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
    public function getProviderClass()
    {
        return SymfonyAccessControlProvider::class;
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
            $requestContext = $this->createRequestContext($routeContext);
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
     *
     * @return RequestContext
     */
    private function createRequestContext(RouteContextInterface $routeContext)
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
