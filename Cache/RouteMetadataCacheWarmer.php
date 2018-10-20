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
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadataCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var RouteMetadataFactory
     */
    private $routeMetadataFactory;

    /**
     * RouteMetadataCacheWarmer constructor.
     *
     * @param RouteMetadataFactory $routeMetadataFactory
     */
    public function __construct(RouteMetadataFactory $routeMetadataFactory)
    {
        $this->routeMetadataFactory = $routeMetadataFactory;
    }

    /**
     * {@inheritdoc}
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
        $this->routeMetadataFactory->warmUp();
    }
}
