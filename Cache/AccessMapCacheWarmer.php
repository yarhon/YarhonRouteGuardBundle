<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Yarhon\RouteGuardBundle\Security\AccessMapBuilder;
use Yarhon\RouteGuardBundle\Security\AccessMapInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var AccessMapBuilder
     */
    private $accessMapBuilder;

    /**
     * @var AccessMapBuilder
     */
    private $accessMap;

    /**
     * AccessMapCacheWarmer constructor.
     *
     * @param AccessMapBuilder   $accessMapBuilder
     * @param AccessMapInterface $accessMap
     */
    public function __construct(AccessMapBuilder $accessMapBuilder, AccessMapInterface $accessMap)
    {
        $this->accessMapBuilder = $accessMapBuilder;
        $this->accessMap = $accessMap;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->accessMapBuilder->build($this->accessMap);
    }
}
