<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Yarhon\RouteGuardBundle\Routing\UrlDeferredInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;
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

    public function __construct(AccessMapBuilderInterface $accessMapBuilder)
    {
        $this->accessMap = $accessMapBuilder->build(); // TODO: process exceptions during build
    }

    public function getTests($routeName, $method = 'GET', UrlDeferredInterface $urlDeferred = null)
    {
        $tests = [];

        $testBags = $this->accessMap->get($routeName);

        foreach ($testBags as $providerName => $testBag) {

            if (!isset($this->testResolvers[$providerName])) {
                throw new RuntimeException(sprintf('No resolver exists for provider "%"', $providerName));
            }

            $resolver = $this->testResolvers[$providerName];

            // TODO: pass $method, $urlDeferred
            $tests = array_merge($tests, $resolver->resolve($testBag));
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
