<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Functional;

use Symfony\Bundle\TwigBundle\TwigBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityTest extends WebTestCase
{
    protected static $bundles = [
        TwigBundle::class,
        SensioFrameworkExtraBundle::class,
        Bundle\SensioSecurityBundle\SensioSecurityBundle::class,
    ];

    protected static $routeResources = [
        ['@SensioSecurityBundle/Controller/', '/', 'annotation'],
    ];

    protected static $users = [
        'bob' => ['password' => 'pa$$word', 'roles' => 'ROLE_USER'],
    ];

    /**
     * @dataProvider linkDataProvider
     */
    public function testLink($user, $route, $expected)
    {
        if ($user) {
            $serverOptions = ['PHP_AUTH_USER' => $user, 'PHP_AUTH_PW' => static::$users[$user]['password']];
        } else {
            $serverOptions = [];
        }

        $client = static::createClient([], $serverOptions);

        $uri = '/link2/'.$route[0];

        $query = array_filter([
            'parameters' => isset($route[1]) ? $route[1] : null,
            'method' => isset($route[2]) ? $route[2] : null,
        ]);

        if ($query) {
            $uri .= '?'.http_build_query($query);
        }

        $crawler = $client->request('GET', $uri);

        //var_dump($crawler->html());

        $link = $crawler->filterXPath('//*[@id="link"]');
        $this->assertEquals($expected, $link->html());
    }

    public function linkDataProvider()
    {
        return [
            [null, ['public_action'], 'http://example.com/public_action'],
            [null, ['user_action'], 'No access'],
            [null, ['admin_action'], 'No access'],

            ['bob', ['public_action'], 'http://example.com/public_action'],
            ['bob', ['user_action'], 'http://example.com/user_action'],
            ['bob', ['admin_action'], 'No access'],
        ];
    }
}
