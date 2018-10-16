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
use Yarhon\RouteGuardBundle\Exception\ExceptionInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMap implements AccessMapInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function set($routeName, array $testBags)
    {
        $key = $this->fixCacheKey($routeName);
        $cacheItem = $this->cache->getItem($key);

        $cacheItem->set($testBags);

        return $this->cache->save($cacheItem);
    }

    /**
     * {@inheritdoc}
     */
    public function get($routeName)
    {
        $key = $this->fixCacheKey($routeName);
        $cacheItem = $this->cache->getItem($key);

        return $cacheItem->get();
    }

    /**
     * {@inheritdoc}
     */
    public function has($routeName)
    {
        $key = $this->fixCacheKey($routeName);

        return $this->cache->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->cache->clear();
    }

    /**
     * @see \Symfony\Component\Cache\CacheItem::validateKey
     *
     * @param string $key
     *
     * @return string
     */
    private function fixCacheKey($key)
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '#', $key);
    }
}
