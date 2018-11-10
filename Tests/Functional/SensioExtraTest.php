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
 * @runTestsInSeparateProcesses
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioExtraTest extends WebTestCase
{
    protected static $bundles = [
        TwigBundle::class,
        SensioFrameworkExtraBundle::class,
        Bundle\SensioExtraBundle\SensioExtraBundle::class,
    ];

    protected static $configs = [
        'framework' => [
            'router' => [
                'resource' => '@SensioExtraBundle/routes.yaml',
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

            [null, ['is_granted_user_role'], 'No access'],
            ['bob', ['is_granted_user_role'], 'http://example.com/is_granted/user_role'],

            [null, ['security_user_role'], 'No access'],
            ['bob', ['security_user_role'], 'http://example.com/security/user_role'],

            ['bob', ['is_granted_admin_role'], 'No access'],
            ['bob', ['security_admin_role'], 'No access'],

            [null, ['security_controller_argument', ['argument' => 5]], 'No access'],
            [null, ['security_controller_argument', ['argument' => 10]], 'http://example.com/security/controller_argument/10'],
        ];
    }
}
