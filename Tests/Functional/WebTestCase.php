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
    protected static $bundles = [];

    protected static $configs = [];

    protected static $users = [];

    protected static function getBundles()
    {
        return array_merge([
            FrameworkBundle::class,
            SecurityBundle::class,
            YarhonRouteGuardBundle::class,
        ], static::$bundles);
    }

    protected static function getConfigs()
    {
        $configs = static::$configs;
        $configs['security']['providers']['main']['memory']['users'] = static::$users;
        // $configs['framework']['router'] = $routerConfig;

        return $configs;
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

    protected static function createKernel(array $options = [])
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

    protected function requestLink($user, $route)
    {
        if ($user) {
            $serverOptions = ['PHP_AUTH_USER' => $user, 'PHP_AUTH_PW' => static::$users[$user]['password']];
        } else {
            $serverOptions = [];
        }

        $client = static::createClient([], $serverOptions);

        $uri = '/link/'.$route[0];

        $query = array_filter([
            'parameters' => isset($route[1]) ? $route[1] : null,
            'method' => isset($route[2]) ? $route[2] : null,
        ]);

        if ($query) {
            $uri .= '?'.http_build_query($query);
        }

        $crawler = $client->request('GET', $uri);

        return $crawler->filterXPath('//*[@id="link"]');
    }
}
