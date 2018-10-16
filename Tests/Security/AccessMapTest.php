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
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Yarhon\RouteGuardBundle\Security\AccessMap;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapTest extends TestCase
{
    private $cache;

    private $cacheMock;

    private $testBags;

    public function setUp()
    {
        $this->cache = new ArrayAdapter();
        $this->cacheMock = $this->createMock(CacheItemPoolInterface::class);

        $this->testBags = [new TestBag([new TestArguments(['ROLE_USER'])])];
    }

    public function testSet()
    {
        $accessMap = new AccessMap($this->cache);

        $result = $accessMap->set('blog', $this->testBags);

        $this->assertTrue($result);
    }

    public function testGet()
    {
        $accessMap = new AccessMap($this->cache);

        $accessMap->set('blog', $this->testBags);

        $this->assertEquals($accessMap->get('blog'), $this->testBags);
        $this->assertNull($accessMap->get('blog1'));
    }

    public function testHas()
    {
        $accessMap = new AccessMap($this->cache);

        $accessMap->set('blog', $this->testBags);

        $this->assertTrue($accessMap->has('blog'));
        $this->assertFalse($accessMap->has('blog1'));
    }

    public function testClear()
    {
        $accessMap = new AccessMap($this->cache);

        $accessMap->set('blog', $this->testBags);

        $result = $accessMap->clear();

        $this->assertTrue($result);
        $this->assertFalse($accessMap->has('blog'));
    }

    public function testSpecialSymbols()
    {
        $accessMap = new AccessMap($this->cache);
        $accessMap->set('blog{}()/\@:', $this->testBags);

        $this->assertTrue($accessMap->has('blog{}()/\@:'));
        $this->assertEquals($accessMap->get('blog{}()/\@:'), $this->testBags);
    }

    public function testSetFailure()
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);

        $this->cacheMock->method('getItem')
            ->willReturn($cacheItem);

        $this->cacheMock->method('save')
            ->willReturn(false);

        $accessMap = new AccessMap($this->cacheMock);

        $result = $accessMap->set('blog', $this->testBags);

        $this->assertFalse($result);
    }

    public function testClearFailure()
    {
        $this->cacheMock->method('clear')
            ->willReturn(false);

        $accessMap = new AccessMap($this->cacheMock);

        $this->assertFalse($accessMap->clear());
    }
}
