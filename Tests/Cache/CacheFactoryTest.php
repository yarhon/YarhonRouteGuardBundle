<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Yarhon\RouteGuardBundle\Cache\CacheFactory;


/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class CacheFactoryTest extends TestCase
{
    public function testGeneral()
    {
        $cache = CacheFactory::create();

        $this->assertInstanceOf(AdapterInterface::class, $cache);
    }

    /**
     * @requires function opcache_invalidate
     * @runInSeparateProcess
     */
    public function testOpcache()
    {
        if (!ini_get('opcache.enable')) {
            $this->markTestSkipped();
        }

        $cache = CacheFactory::create();

        $this->assertInstanceOf(PhpFilesAdapter::class, $cache);
    }

    /**
     * @runInSeparateProcess
     */
    public function testFilesystem()
    {
        ini_set('opcache.enable', 0);

        $cache = CacheFactory::create();

        $this->assertInstanceOf(FilesystemAdapter::class, $cache);
    }
}
