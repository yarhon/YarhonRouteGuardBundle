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

    public function testAction1()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/action1');
        $crawler = $crawler->filterXPath('//body');

        $this->assertEquals('http://example.com/action1', $crawler->html());
    }
}
