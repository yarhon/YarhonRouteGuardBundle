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
     * @var AccessMapInterface
     */
    private $accessMap;

    /**
     * @var TestResolverInterface
     */
    private $testResolver;

    public function __construct(AccessMapInterface $accessMap, TestResolverInterface $testResolver)
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

        //var_dump($this->accessMap->has('blog1'), $this->accessMap->get('blog1'));

        $testBags = $this->accessMap->get($routeContext->getName());

        if (null === $testBags) {
            return [];
        }

        $tests = [];
        foreach ($testBags as $testBag) {
            $tests = array_merge($tests, $this->testResolver->resolve($testBag, $routeContext));
        }

        if ($this->logger) {
            // ....................
        }

        return $tests;
    }
}
