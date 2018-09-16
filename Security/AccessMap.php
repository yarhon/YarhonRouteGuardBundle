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

    public function __construct(CacheItemPoolInterface $cache = null)
    {
        $this->cache = $cache ?: new ArrayAdapter();
    }

    /**
     * {@inheritdoc}
     */
    public function add($routeName, array $testBags)
    {
        $cacheItem = $this->cache->getItem('tests//'.$routeName);

        $cacheItem->set($testBags);
        $this->cache->save($cacheItem);
    }

    /**
     * {@inheritdoc}
     */
    public function get($routeName)
    {
        $cacheItem = $this->cache->getItem('tests//'.$routeName);

        return $cacheItem->get();
    }

    /**
     * {@inheritdoc}
     */
    public function has($routeName)
    {
        return $this->cache->hasItem('tests//'.$routeName);
    }

    /**
     * {@inheritdoc}
     */
    public function addException($routeName, ExceptionInterface $exception = null)
    {
        $cacheItem = $this->cache->getItem('exceptions//'.$routeName);

        $cacheItem->set($exception);
        $this->cache->save($cacheItem);
    }

    /**
     * {@inheritdoc}
     */
    public function getException($routeName)
    {
        $cacheItem = $this->cache->getItem('exceptions//'.$routeName);

        return $cacheItem->get();
    }

    /**
     * {@inheritdoc}
     */
    public function hasException($routeName)
    {
        return $this->cache->hasItem('exceptions//'.$routeName);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->cache->clear();
    }
}
