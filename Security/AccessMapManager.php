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
use Yarhon\RouteGuardBundle\Security\Http\TestBagMapInterface;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMapResolverInterface;
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

    /**
     * @var TestBagMapResolverInterface
     */
    private $testBagMapResolver;

    public function __construct(AccessMapBuilderInterface $accessMapBuilder, TestBagMapResolverInterface $testBagMapResolver = null)
    {
        $this->accessMap = $accessMapBuilder->build(); // TODO: process exceptions during build
        $this->testBagMapResolver = $testBagMapResolver;
    }

    public function getTests($routeName, $method = 'GET', UrlDeferredInterface $urlDeferred = null)
    {
        $tests = [];

        $testBags = $this->accessMap->get($routeName);

        foreach ($testBags as $providerName => $testBag) {
            if ($testBag instanceof TestBagMapInterface) {
                if (null === $this->testBagMapResolver) {
                    throw new RuntimeException('Unable to resolve TestBagMapInterface instance because TestBagMapResolver service is not provided.');
                }

                $testBag = $this->testBagMapResolver->resolve($testBag, $method, $urlDeferred);
                if (null === $testBag) {
                    continue;
                };
            }

            if (!isset($this->testResolvers[$providerName])) {
                throw new RuntimeException(sprintf('No resolver exists for provider "%"', $providerName));
            }

            $resolver = $this->testResolvers[$providerName];

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
