<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection\Container;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ForeignExtensionAccessor
{
    /**
     * @var ContainerBuilder;
     */
    private $container;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * ForeignExtensionAccessor constructor.
     * @param ContainerBuilder $container
     * @param Processor|null   $processor
     */
    public function __construct(ContainerBuilder $container, Processor $processor = null)
    {
        $this->container = $container;
        $this->processor = $processor ?: new Processor();
    }

    /**
     * Returns processed config of a foreign extension outside of it's context.
     *
     * @param string $extensionName
     *
     * @return array Processed configuration
     *
     * @throws \LogicException When extension configuration class in not an instance of ConfigurationExtensionInterface
     */
    public function getProcessedConfig($extensionName)
    {
        $extension = $this->container->getExtension($extensionName);

        if (!($extension instanceof ConfigurationExtensionInterface)) {
            throw new \LogicException(sprintf('"%s" extension configuration class is not an instance of %s.',
                $extensionName, ConfigurationExtensionInterface::class));
        }

        $configuration = $extension->getConfiguration([], $this->container);
        $configs = $this->container->getExtensionConfig($extensionName);
        $processed = $this->processor->processConfiguration($configuration, $configs);

        return $processed;
    }
}
