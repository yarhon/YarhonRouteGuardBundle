<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var AccessMapBuilder
     */
    private $accessMapBuilder;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * RouteCacheWarmer constructor.
     *
     * @param AccessMapBuilder $accessMapBuilder
     * @param string           $cacheDir
     */
    public function __construct(AccessMapBuilder $accessMapBuilder, $cacheDir = null)
    {
        $this->accessMapBuilder = $accessMapBuilder;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return false
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
        var_dump('route cache warmer');

        $cacheDir = $this->createCacheDir($cacheDir);

        $this->accessMapBuilder->build();
    }

    /**
     * @param string $baseDir
     *
     * @return string $cacheDir
     *
     * @throws \RuntimeException
     */
    private function createCacheDir($baseDir)
    {
        $cacheDir = $baseDir.\DIRECTORY_SEPARATOR.$this->cacheDir;

        if (!is_dir($cacheDir)) {
            if (false === @mkdir($cacheDir, 0777, true)) {
                throw new \RuntimeException(sprintf('Unable to create the LinkGuard Bundle cache directory "%s".', $cacheDir));
            }
        } elseif (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf('The LinkGuard Bundle cache directory "%s" is not writable for the current system user.', $cacheDir));
        }

        return $cacheDir;
    }
}
