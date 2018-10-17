<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\DependencyInjection\Configuration;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    public function testDefaults()
    {
        $input = [];

        $processor = $this->createPartialMock('Symfony\Component\Config\Definition\Processor', []);

        $config = $processor->processConfiguration(new Configuration(), [$input]);

        $defaults = [
            'ignore_controllers' => [
                'Symfony\Bundle\TwigBundle\Controller\PreviewErrorController',
                'Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController',
                'Symfony\Bundle\WebProfilerBundle\Controller\RouterController',
                'Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController',
            ],
            'twig' => [
                'tag_name' => 'route',
                'tag_variable_name' => '_route',
                'discover_routing_functions' => true,
            ],
        ];

        $this->assertEquals($defaults, $config);

        $this->markTestIncomplete('Watch for config changes.');
    }
}
