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
use Psr\Cache\CacheItemPoolInterface;
use Yarhon\RouteGuardBundle\Cache\CacheFactory;
use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestLoader implements TestLoaderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var CacheItemPoolInterface
     */
    private $testsCache;

    /**
     * @var TestResolverInterface
     */
    private $testResolver;

    public function __construct(CacheItemPoolInterface $testsCache, TestResolverInterface $testResolver)
    {
        $this->testsCache = $testsCache;
        $this->testResolver = $testResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(RouteContextInterface $routeContext)
    {
        // TODO: add @throws to interface doc

        //var_dump($this->accessMap->has('blog1'), $this->accessMap->get('blog1'));
        $cacheKey = CacheFactory::getValidCacheKey($routeContext->getName());
        $cacheItem = $this->testsCache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            return [];
        }

        $testBags = $cacheItem->get();

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
