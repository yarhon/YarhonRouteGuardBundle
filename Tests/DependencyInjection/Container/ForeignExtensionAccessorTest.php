<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\DependencyInjection\Container;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\Exception as ConfigDefinitionException;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ForeignExtensionAccessor;
use Yarhon\RouteGuardBundle\Exception\LogicException;

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
            ],
        ];

        $configuration = $this->createMock(ConfigurationInterface::class);

        $extension = $this->createMock(Extension::class);
        $extension->method('getAlias')
            ->willReturn('foreign');
        $extension->method('getConfiguration')
            ->willReturn($configuration);

        $processor = $this->createMock(Processor::class);
        $processor->expects($this->once())
            ->method('processConfiguration')
            ->with($configuration, $rawConfig)
            ->willReturn(['processed' => true]);

        $accessor = new ForeignExtensionAccessor($processor);
        $container = new ContainerBuilder();
        $container->registerExtension($extension);

        $container->loadFromExtension('foreign', $rawConfig[0]);
        $container->loadFromExtension('foreign', $rawConfig[1]);

        $config = $accessor->getProcessedConfig($container, $extension);

        $this->assertEquals(['processed' => true], $config);
    }

    public function testGetProcessedConfigNotInstanceOfConfigurationExtensionException()
    {
        $extension = $this->createMock(ExtensionInterface::class);
        $extension->method('getAlias')
            ->willReturn('foreign');

        $processor = $this->createMock(Processor::class);

        $accessor = new ForeignExtensionAccessor($processor);
        $container = new ContainerBuilder();
        $container->registerExtension($extension);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('"%s" extension class is not an instance of %s.', 'foreign', ConfigurationExtensionInterface::class));

        $accessor->getProcessedConfig($container, $extension);
    }

    public function testGetProcessedConfigConfigDefinitionException()
    {
        $configuration = $this->createMock(ConfigurationInterface::class);

        $extension = $this->createMock(Extension::class);
        $extension->method('getAlias')
            ->willReturn('foreign');
        $extension->method('getConfiguration')
            ->willReturn($configuration);

        $processor = $this->createMock(Processor::class);
        $processor->method('processConfiguration')
            ->willThrowException(new ConfigDefinitionException('test'));

        $accessor = new ForeignExtensionAccessor($processor);
        $container = new ContainerBuilder();
        $container->registerExtension($extension);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot read configuration of the "foreign" extension because of configuration exception.');

        $accessor->getProcessedConfig($container, $extension);
    }
}
