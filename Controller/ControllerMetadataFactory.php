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
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
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
     * ControllerMetadataFactory constructor.
     *
     * @param ControllerNameResolverInterface       $controllerNameResolver
     * @param ArgumentMetadataFactoryInterface|null $argumentMetadataFactory
     */
    public function __construct(ControllerNameResolverInterface $controllerNameResolver, ArgumentMetadataFactoryInterface $argumentMetadataFactory = null)
    {
        $this->controllerNameResolver = $controllerNameResolver;
        $this->argumentMetadataFactory = $argumentMetadataFactory ?: new ArgumentMetadataFactory();
    }

    /**
     * @param Route $route
     *
     * @return ControllerMetadata|null
     *
     * @throws RuntimeException
     */
    public function createMetadata(Route $route)
    {
        $controller = $route->getDefault('_controller');

        // TODO: check exceptions here?
        $controllerName = $this->controllerNameResolver->resolve($controller);

        if (null === $controllerName) {
            return null;
        }

        list($class, $method) = explode('::', $controllerName);
        $arguments = $this->argumentMetadataFactory->createArgumentMetadata([$class, $method]);

        return new ControllerMetadata($controllerName, $arguments);
    }
}
