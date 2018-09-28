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
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Yarhon\RouteGuardBundle\YarhonRouteGuardBundle;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
abstract class WebTestCase extends BaseWebTestCase
{
    protected static function getBundles()
    {
        return [
            FrameworkBundle::class,
            SecurityBundle::class,
            YarhonRouteGuardBundle::class,
        ];
    }

    protected static function getConfigs()
    {
        $configs = [
            'framework' => [
                'secret' => 'foo',
                'test' => null,
            ],
            'security' => [
                'firewalls' => [
                    'main' => ['anonymous' => true],
                ],
            ],
            'twig' => ['debug' => true],
        ];

        if ($routerConfig = static::getRouterConfig()) {
            $configs['framework']['router'] = $routerConfig;
        }

        return $configs;
    }

    protected static function getRouterConfig()
    {
        return [];
    }

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
        return new app\Kernel(
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

    protected static function createClient(array $options = [], array $server = [])
    {
        $server = array_merge([
            'HTTP_HOST' => 'example.com',
        ], $server);

        return parent::createClient($options, $server);
    }
}
