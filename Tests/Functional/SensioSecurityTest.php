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
    protected static function getBundles()
    {
        return array_merge(parent::getBundles(), [
            TwigBundle::class,
            SensioFrameworkExtraBundle::class,
            Bundle\SensioSecurityBundle\SensioSecurityBundle::class,
        ]);
    }

    protected static function getRouterConfig()
    {
        return [
            'resource' => '@SensioSecurityBundle/Controller/',
            'type' => 'annotation',
        ];
    }

    public function testNotSecured()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $publicLink = $crawler->filterXPath('//*[@id="public_link"]');
        $userLink = $crawler->filterXPath('//*[@id="user_link"]');
        $adminLink = $crawler->filterXPath('//*[@id="admin_link"]');

        $this->assertEquals('http://example.com/public_action', $publicLink->html());
        $this->assertEquals('No access', $userLink->html());
        $this->assertEquals('No access', $adminLink->html());
    }

    public function testIsGrantedAnnotation()
    {
        $client = static::createClient([], ['PHP_AUTH_USER' => 'bob', 'PHP_AUTH_PW' => 'pa$$word']);
        $crawler = $client->request('GET', '/');

        $publicLink = $crawler->filterXPath('//*[@id="public_link"]');
        $userLink = $crawler->filterXPath('//*[@id="user_link"]');
        $adminLink = $crawler->filterXPath('//*[@id="admin_link"]');

        $this->assertEquals('http://example.com/public_action', $publicLink->html());
        $this->assertEquals('http://example.com/user_action', $userLink->html());
        $this->assertEquals('No access', $adminLink->html());
    }
}
