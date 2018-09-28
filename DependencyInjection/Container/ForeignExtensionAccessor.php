<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\DependencyInjection\Container;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Exception\Exception as ConfigDefinitionException;
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ForeignExtensionAccessor
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * ForeignExtensionAccessor constructor.

     *
     * @param Processor|null $processor
     */
    public function __construct(Processor $processor = null)
    {
        $this->processor = $processor ?: new Processor();
    }

    /**
     * Returns processed config of a foreign extension outside of it's context.
     *
     * @param ContainerBuilder $container
     * @param string           $extensionName
     *
     * @return array Processed configuration
     *
     * @throws LogicException When extension configuration class in not an instance of ConfigurationExtensionInterface
     */
    public function getProcessedConfig(ContainerBuilder $container, $extensionName)
    {
        $extension = $container->getExtension($extensionName);

        if (!($extension instanceof ConfigurationExtensionInterface)) {
            throw new LogicException(sprintf('"%s" extension configuration class is not an instance of %s.',
                $extensionName, ConfigurationExtensionInterface::class));
        }

        $configuration = $extension->getConfiguration([], $container);
        $configs = $container->getExtensionConfig($extensionName);

        try {
            $processed = $this->processor->processConfiguration($configuration, $configs);
        } catch (ConfigDefinitionException $e) {
            throw new LogicException(sprintf('Cannot read configuration of the "%s" extension because of configuration exception.', $extensionName), 0, $e);
        }

        return $processed;
    }
}
