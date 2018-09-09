<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteTestResolver implements RouteTestResolverInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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

    /**
     * {@inheritdoc}
     */
    public function getTests(RouteContextInterface $routeContext)
    {
        // TODO: add @throws to interface doc

        $tests = [];

        $testBags = $this->accessMap->get($routeContext->getName());

        foreach ($testBags as $testBag) {
            $tests = array_merge($tests, $this->testResolver->resolve($testBag, $routeContext));
        }

        if ($this->logger) {
            // ....................
        }

        return $tests;
    }
}
