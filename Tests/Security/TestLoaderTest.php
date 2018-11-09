<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Security\TestBagResolver\TestBagResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\TestLoader;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestLoaderTest extends TestCase
{
    private $testsCache;

    private $testBagResolver;

    private $loader;

    public function setUp()
    {
        $this->testsCache = new ArrayAdapter(0, false);

        $this->testBagResolver = $this->createMock(TestBagResolverInterface::class);

        $this->loader = new TestLoader($this->testsCache, $this->testBagResolver);
    }

    private function addTestsCacheItem($name, $value)
    {
        $cacheItem = $this->testsCache->getItem($name);
        $cacheItem->set($value);
        $this->testsCache->save($cacheItem);
    }

    public function testLoad()
    {
        $testOne = $this->createMock(TestInterface::class);
        $testTwo = $this->createMock(TestInterface::class);

        $this->testBagResolver->method('resolve')
            ->willReturnOnConsecutiveCalls([$testOne], [$testTwo]);

        $testBagOne = $this->createMock(AbstractTestBagInterface::class);
        $testBagTwo = $this->createMock(AbstractTestBagInterface::class);

        $this->addTestsCacheItem('index', [$testBagOne, $testBagTwo]);

        $routeContext = new RouteContext('index');

        $tests = $this->loader->load($routeContext);

        $this->assertSame([$testOne, $testTwo], $tests);
    }

    public function testLoadNoCacheItem()
    {
        $routeContext = new RouteContext('index');
        $tests = $this->loader->load($routeContext);
        $this->assertSame([], $tests);
    }
}
