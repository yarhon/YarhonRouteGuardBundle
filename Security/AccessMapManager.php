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
class AccessMapManager
{
    /**
     * @var AccessMap
     */
    private $accessMap;

    /**
     * @var TestResolverInterface[]
     */
    private $testResolvers;

    public function __construct(AccessMap $accessMap)
    {
        $this->accessMap = $accessMap;
    }

    public function getTests(RouteContextInterface $routeContext)
    {
        $tests = [];

        $testBags = $this->accessMap->get($routeContext->getName());

        foreach ($testBags as $providerName => $testBag) {

            if (!isset($this->testResolvers[$providerName])) {
                throw new RuntimeException(sprintf('No resolver exists for provider "%"', $providerName));
            }

            $resolver = $this->testResolvers[$providerName];

            $tests = array_merge($tests, $resolver->resolve($testBag, $routeContext));
        }

        return $tests;
    }

    /**
     * @param TestResolverInterface $resolver
     */
    public function addTestResolver(TestResolverInterface $resolver)
    {
        $this->testResolvers[$resolver->getName()] = $resolver;
    }

    /**
     * @param TestResolverInterface[] $resolvers
     */
    public function setTestResolvers(array $resolvers)
    {
        $this->testResolvers = [];

        foreach ($resolvers as $resolver) {
            $this->addTestResolver($resolver);
        }
    }

}
