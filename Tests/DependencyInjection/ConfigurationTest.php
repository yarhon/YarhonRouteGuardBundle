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
use Symfony\Component\Config\Definition\Processor;
use Yarhon\RouteGuardBundle\DependencyInjection\Configuration;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    public function testDefaults()
    {
        $input = [];

        $processor = $this->createPartialMock(Processor::class, []);

        $config = $processor->processConfiguration(new Configuration(), [$input]);

        $defaults = [
            'data_collector' => [
                'ignore_controllers' => [
                    'twig.controller.preview_error',
                    'web_profiler.controller.profiler',
                    'web_profiler.controller.router',
                    'web_profiler.controller.exception',
                ],
                'ignore_exceptions' => false,
            ],
            'twig' => [
                'tag_name' => 'route',
                'tag_variable_name' => '_route',
                'discover_routing_functions' => true,
            ],
        ];

        $this->assertEquals($defaults, $config);
    }
}
