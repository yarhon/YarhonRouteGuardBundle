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
use Yarhon\RouteGuardBundle\Controller\ControllerMetadataFactory;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerMetadataCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var ControllerMetadataFactory
     */
    private $controllerMetadataFactory;

    /**
     * ControllerMetadataCacheWarmer constructor.
     *
     * @param ControllerMetadataFactory $controllerMetadataFactory
     */
    public function __construct(ControllerMetadataFactory $controllerMetadataFactory)
    {
        $this->controllerMetadataFactory = $controllerMetadataFactory;
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
        $this->controllerMetadataFactory->warmUp();
    }
}
