<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Cache;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
trait CacheKeyTrait
{
    private $encodedChars = [
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
     * @param string $key
     *
     * @return string
     */
    private function getValidCacheKey($key)
    {
        return strtr($key, $this->encodedChars);
    }
}
