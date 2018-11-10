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

/**
 * @runTestsInSeparateProcesses
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlTest extends WebTestCase
{
    protected static $bundles = [
        TwigBundle::class,
        Bundle\SymfonyAccessControlBundle\SymfonyAccessControlBundle::class,
    ];

    protected static $configs = [
        'framework' => [
            'router' => [
                'resource' => '@SymfonyAccessControlBundle/routes.yaml',
            ],
        ],
        'security' => [
            'access_control' => [
                ['path' => 'foo', 'roles' => 'ROLE_DUMMY'],
                ['path' => '^/static_path', 'roles' => 'ROLE_USER'],
                ['path' => '^/dynamic_path/user', 'roles' => 'ROLE_USER'],
                ['path' => '^/dynamic_path/admin', 'roles' => 'ROLE_ADMIN'],
            ],
        ],
    ];

    protected static $users = [
        'bob' => ['password' => 'pa$$word', 'roles' => 'ROLE_USER'],
    ];

    /**
     * @dataProvider linkDataProvider
     */
    public function testLink($user, $route, $expected)
    {
        $link = $this->requestLink($user, $route);
        $this->assertEquals($expected, $link->html());
    }

    public function linkDataProvider()
    {
        return [
            [null, ['public'], 'http://example.com/public'],
            ['bob', ['public'], 'http://example.com/public'],

            [null, ['static_path'], 'No access'],
            ['bob', ['static_path'], 'http://example.com/static_path'],

            [null, ['dynamic_path', ['page' => 'test']], 'http://example.com/dynamic_path/test'],
            [null, ['dynamic_path', ['page' => 'user']], 'No access'],
            ['bob', ['dynamic_path', ['page' => 'user']], 'http://example.com/dynamic_path/user'],
            ['bob', ['dynamic_path', ['page' => 'admin']], 'No access'],

        ];
    }
}
