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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Yarhon\RouteGuardBundle\Cache\CacheFactory;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class CacheFactoryTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        static::deleteTempDir();
    }

    public static function tearDownAfterClass()
    {
        static::deleteTempDir();
    }

    public function testGeneral()
    {
        $cache = CacheFactory::createCache(self::getTempDir(), 'test');

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

        $cache = CacheFactory::createCache(self::getTempDir(), 'test');

        $this->assertInstanceOf(PhpFilesAdapter::class, $cache);
    }

    public function testGetGetValidCacheKey()
    {
        $key = 'index{}()/\\@:route';

        $validKey = CacheFactory::getValidCacheKey($key);

        $this->assertEquals('index%7B%7D%28%29%2F%5C%40%3Aroute', $validKey);
    }

    /**
     * @runInSeparateProcess
     */
    public function testFilesystem()
    {
        ini_set('opcache.enable', 0);

        $cache = CacheFactory::createCache(self::getTempDir(), 'test');

        $this->assertInstanceOf(FilesystemAdapter::class, $cache);
    }

    protected static function getTempDir()
    {
        return sys_get_temp_dir().'/route-guard-'.substr(strrchr(static::class, '\\'), 1);
    }

    protected static function deleteTempDir()
    {
        if (!file_exists($dir = static::getTempDir())) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
    }
}
