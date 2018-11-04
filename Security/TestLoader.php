<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Psr\Cache\CacheItemPoolInterface;
use Yarhon\RouteGuardBundle\Cache\CacheFactory;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\TestBagResolver\TestBagResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;

/**
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestLoader implements TestLoaderInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $testsCache;

    /**
     * @var TestBagResolverInterface
     */
    private $testBagResolver;

    /**
     * @param CacheItemPoolInterface   $testsCache
     * @param TestBagResolverInterface $testBagResolver
     */
    public function __construct(CacheItemPoolInterface $testsCache, TestBagResolverInterface $testBagResolver)
    {
        $this->testsCache = $testsCache;
        $this->testBagResolver = $testBagResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function load(RouteContextInterface $routeContext)
    {
        $cacheKey = CacheFactory::getValidCacheKey($routeContext->getName());
        $cacheItem = $this->testsCache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            return [];
        }

        $testBags = $cacheItem->get();

        $tests = [];
        foreach ($testBags as $testBag) {
            /** @var AbstractTestBagInterface $testBag */
            $providerClass = $testBag->getProviderClass();
            $providerTests = $this->testBagResolver->resolve($testBag, $routeContext);
            foreach ($providerTests as $test) {
                $test->setProviderClass($providerClass);
                $tests[] = $test;
            }
        }

        return $tests;
    }
}
