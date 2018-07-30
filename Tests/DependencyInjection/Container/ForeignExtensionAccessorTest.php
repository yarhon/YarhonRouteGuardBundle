<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\DependencyInjection\Container;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Yarhon\LinkGuardBundle\DependencyInjection\Container\ForeignExtensionAccessor;
use Yarhon\LinkGuardBundle\Tests\Fixtures\DependencyInjection\ForeignExtension;
use Yarhon\LinkGuardBundle\Tests\Fixtures\DependencyInjection\Configuration;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ForeignExtensionAccessorTest extends TestCase
{
    public function testGetProcessedConfig()
    {
        $rawConfig = [
            [
                'key1' => 'value1',
            ],
            [
                'key2' => 'value2',
            ]
        ];

        $configuration = $this->createMock(ConfigurationInterface::class);

        $extension = $this->createMock(Extension::class);
        $extension->method('getAlias')
            ->willReturn('foreign');
        $extension->method('getConfiguration')
            ->willReturn($configuration);

        $processor = $this->createMock(Processor::class);
        $processor->method('processConfiguration')
            ->willReturn(['processed' => true]);
        $processor->expects($this->once())
            ->method('processConfiguration')
            ->with($configuration, $rawConfig);

        $accessor = new ForeignExtensionAccessor($processor);
        $container = new ContainerBuilder();
        $container->registerExtension($extension);

        $container->loadFromExtension('foreign', $rawConfig[0]);
        $container->loadFromExtension('foreign', $rawConfig[1]);

        $config = $accessor->getProcessedConfig($container, 'foreign');

        $this->assertEquals(['processed' => true], $config);
    }
}
