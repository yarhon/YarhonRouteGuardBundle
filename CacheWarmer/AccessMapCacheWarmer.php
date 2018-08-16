<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Yarhon\RouteGuardBundle\Security\AccessMapBuilderInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var AccessMapBuilderInterface
     */
    private $accessMapBuilder;

    /**
     * AccessMapCacheWarmer constructor.
     *
     * @param AccessMapBuilderInterface $accessMapBuilder
     */
    public function __construct(AccessMapBuilderInterface $accessMapBuilder)
    {
        $this->accessMapBuilder = $accessMapBuilder;
    }

    /**
     * @return false
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->accessMapBuilder->build(true);
    }
}
