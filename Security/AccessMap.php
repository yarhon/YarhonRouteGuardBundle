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
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMap
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache = null)
    {
        $this->cache = $cache ?: new ArrayAdapter();
    }

    /**
     * @param string                   $routeName
     * @param string                   $providerName
     * @param AbstractTestBagInterface $testBag
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function add($routeName, $providerName, $testBag)
    {
        $cacheItem = $this->cache->getItem($routeName);

        $testBags = $cacheItem->isHit() ? $cacheItem->get() : [];
        $testBags[$providerName] = $testBag;

        $cacheItem->set($testBags);
        $this->cache->save($cacheItem);
    }

    /**
     * @param string $routeName
     *
     * @return AbstractTestBagInterface[]
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get($routeName)
    {
        $cacheItem = $this->cache->getItem($routeName);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        return [];
    }

    public function clear()
    {
        $this->cache->clear();
    }
}
