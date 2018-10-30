<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Symfony\Component\Routing\Route;
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
     * @var ControllerNameResolverInterface
     */
    private $controllerNameResolver;

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
     * @param ControllerNameResolverInterface       $controllerNameResolver
     * @param ArgumentMetadataFactoryInterface|null $argumentMetadataFactory
     * @param ClassMapInterface                     $containerClassMap
     */
    public function __construct(ControllerNameResolverInterface $controllerNameResolver, ArgumentMetadataFactoryInterface $argumentMetadataFactory = null, ClassMapInterface $containerClassMap = null)
    {
        $this->controllerNameResolver = $controllerNameResolver;
        $this->argumentMetadataFactory = $argumentMetadataFactory ?: new ArgumentMetadataFactory();
        $this->containerClassMap = $containerClassMap;
    }

    /**
     * @param Route $route
     *
     * @return ControllerMetadata|null
     *
     * @throws InvalidArgumentException
     */
    public function createMetadata(Route $route)
    {
        $controller = $route->getDefault('_controller');

        $name = $this->controllerNameResolver->resolve($controller);

        if (null === $name) {
            return null;
        }

        list($class, $method) = explode('::', $name);

        if (null === $serviceClass = $this->detectContainerController($class)) {
            $serviceId = null;
        } else {
            $serviceId = $class;
            $class = $serviceClass;
        }

        $arguments = $this->argumentMetadataFactory->createArgumentMetadata([$class, $method]);

        $class = ltrim($class, '\\');

        return new ControllerMetadata($name, $class, $method, $arguments, $serviceId);
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
