<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Functional;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityTest extends WebTestCase
{
    protected static function getBundles()
    {
        return array_merge(parent::getBundles(), [
            \Symfony\Bundle\TwigBundle\TwigBundle::class,
            \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class,
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

        $client->request('GET', '/action1');

        $content = $client->getResponse()->getContent();

        var_dump($content);
    }
}
