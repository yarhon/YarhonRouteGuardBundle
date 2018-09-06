<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteTestResolver
{
    /**
     * @var AccessMap
     */
    private $accessMap;

    /**
     * @var TestResolverInterface
     */
    private $testResolver;

    public function __construct(AccessMap $accessMap, TestResolverInterface $testResolver)
    {
        $this->accessMap = $accessMap;
        $this->testResolver = $testResolver;
    }

    public function getTests(RouteContextInterface $routeContext)
    {
        $tests = [];

        $testBags = $this->accessMap->get($routeContext->getName());

        foreach ($testBags as $testBag) {
            $tests = array_merge($tests, $this->testResolver->resolve($testBag, $routeContext));
        }

        return $tests;
    }
}
