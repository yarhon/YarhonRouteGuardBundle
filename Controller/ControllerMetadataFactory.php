<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ClassMapInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * To detect "controllers as a service" and retrieve class of those controllers we use ClassMapInterface container class map,
 * since \Symfony\Component\DependencyInjection\ContainerInterface doesn't allow to get actual service class
 * without instantiating it.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerMetadataFactory
{
    /**
     * @var ArgumentMetadataFactoryInterface
     */
    private $argumentMetadataFactory;

    /**
     * @var ClassMapInterface
     */
    private $containerClassMap;

    /**
     * ControllerMetadataFactory constructor.
     *
     * @param ArgumentMetadataFactoryInterface|null $argumentMetadataFactory
     * @param ClassMapInterface|null                $containerClassMap
     */
    public function __construct(ArgumentMetadataFactoryInterface $argumentMetadataFactory = null, ClassMapInterface $containerClassMap = null)
    {
        $this->argumentMetadataFactory = $argumentMetadataFactory ?: new ArgumentMetadataFactory();
        $this->containerClassMap = $containerClassMap;
    }

    /**
     * @param string $controllerName Controller name in class::method (service::method) notation
     *
     * @return ControllerMetadata
     *
     * @throws InvalidArgumentException
     */
    public function createMetadata($controllerName)
    {
        list($class, $method) = explode('::', $controllerName);

        if (null === $serviceClass = $this->detectContainerController($class)) {
            $serviceId = null;
        } else {
            $serviceId = $class;
            $class = $serviceClass;
        }

        $arguments = $this->argumentMetadataFactory->createArgumentMetadata([$class, $method]);

        $class = ltrim($class, '\\');

        return new ControllerMetadata($controllerName, $class, $method, $arguments, $serviceId);
    }

    /**
     * @param string $serviceId
     *
     * @return string|null
     */
    private function detectContainerController($serviceId)
    {
        if (!$this->containerClassMap || !$this->containerClassMap->has($serviceId)) {
            return null;
        }

        $serviceClass = $this->containerClassMap->get($serviceId);

        // Service class in container class map can be null is some cases (i.e., when service is instantiated by a factory method).
        if (null === $serviceClass) {
            throw new InvalidArgumentException(sprintf('Unable to resolve class for service "%s".', $serviceId));
        }

        return $serviceClass;
    }
}
