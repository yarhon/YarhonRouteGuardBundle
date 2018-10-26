<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Cache;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class CacheFactory
{
    private static $keyEncodedChars = [
        '{' => '%7B',
        '}' => '%7D',
        '(' => '%28',
        ')' => '%29',
        '/' => '%2F',
        '\\' => '%5C',
        '@' => '%40',
        ':' => '%3A',
    ];

    /**
     * @param string $directory
     * @param string $namespace
     *
     * @return AdapterInterface
     */
    public static function createCache($directory, $namespace)
    {
        if (PhpFilesAdapter::isSupported()) {
            return new PhpFilesAdapter($namespace, 0, $directory);
        }

        return new FilesystemAdapter($namespace, 0, $directory);
    }

    /**
     * @see \Symfony\Component\Cache\CacheItem::validateKey
     *
     * @param string $key
     *
     * @return string
     */
    public static function getValidCacheKey($key)
    {
        return strtr($key, static::$keyEncodedChars);
    }
}
