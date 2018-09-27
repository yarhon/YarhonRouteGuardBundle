<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
abstract class WebTestCase extends BaseWebTestCase
{
    protected static function getBundles()
    {
        return [
            \Symfony\Bundle\FrameworkBundle\FrameworkBundle::class,
            \Symfony\Bundle\SecurityBundle\SecurityBundle::class,
        ];
    }

    protected static function getConfigs()
    {
        return [
            'framework' => [
                'router' => static::getRouterConfig(),
                'secret' => 'foo',
                'test' => null,
            ],
        ];
    }

    abstract protected static function getRouterConfig();

    public static function setUpBeforeClass()
    {
        static::deleteTempDir();
    }

    public static function tearDownAfterClass()
    {
        static::deleteTempDir();
    }

    protected static function deleteTempDir()
    {
        if (!file_exists($dir = static::getTempDir())) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
    }

    protected static function createKernel(array $options = array())
    {
        return new app\AppKernel(
            static::getTempDir(),
            static::getBundles(),
            static::getConfigs(),
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    protected static function getTempDir()
    {
        return sys_get_temp_dir().'/route-guard-'.substr(strrchr(static::class, '\\'), 1);
    }
}
