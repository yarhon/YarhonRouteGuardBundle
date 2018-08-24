<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Yarhon\RouteGuardBundle\Routing\UrlDeferredInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagMapResolver implements TestBagMapResolverInterface
{
    /**
     * @var RequestContextFactory
     */
    private $requestContextFactory;

    /**
     * @var RequestContextMatcher;
     */
    private $requestContextMatcher;

    /**
     * TestBagMapResolver constructor.
     *
     * @param RequestContextFactory      $requestContextFactory
     * @param RequestContextMatcher|null $requestContextMatcher
     */
    public function __construct(RequestContextFactory $requestContextFactory, RequestContextMatcher $requestContextMatcher = null)
    {
        $this->requestContextFactory = $requestContextFactory;
        $this->requestContextMatcher = $requestContextMatcher ?: new RequestContextMatcher();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(TestBagMapInterface $testBagMap, $method = 'GET', UrlDeferredInterface $urlDeferred = null)
    {
        if (null === $urlDeferred) {
            throw new RuntimeException('Unable to resolve TestBagMapInterface instance because UrlDeferredInterface parameter is not provided.');
        }

        $requestContext = $this->requestContextFactory->create($urlDeferred, $method);

        $resolved = null;

        foreach ($testBagMap as $item) {
            /** @var RequestConstraint $requestConstraint */
            list($testBag, $requestConstraint) = $item;

            if (null === $requestConstraint || $this->requestContextMatcher->matches($requestContext, $requestConstraint)) {
                $resolved = $testBag;
                break;
            }
        }

        return $resolved;
    }
}
