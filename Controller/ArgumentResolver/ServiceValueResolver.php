<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller\ArgumentResolver;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException as ContainerRuntimeException;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * Returns a service used as controller argument.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class ServiceValueResolver implements ArgumentValueResolverInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        $controller = $context->getControllerName();

        if (!$controller) {
            return false;
        }

        if (!$serviceLocator = $this->getServiceLocator($controller)) {
            return false;
        }

        return $serviceLocator->has($argument->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        $controller = $context->getControllerName();
        $serviceLocator = $this->getServiceLocator($controller);

        try {
            return $serviceLocator->get($argument->getName());
        } catch (ContainerRuntimeException $e) {
            $message = $this->transformExceptionMessage($e->getMessage(), $controller, $argument->getName());
            throw new RuntimeException($message, 0, $e);
        }
    }

    private function transformExceptionMessage($originalMessage, $controller, $argument)
    {
        $what = sprintf('argument $%s of "%s()"', $argument, $controller);
        $message = preg_replace('/service "\.service_locator\.[^"]++"/', $what, $originalMessage);

        if ($originalMessage === $message) {
            $message = sprintf('Cannot resolve %s: %s', $what, $message);
        }

        return $message;
    }

    private function getServiceLocator($controller)
    {
        if ($this->container->has($controller)) {
            return $this->container->get($controller);
        }

        // Before Symfony 4.1 controller service locators used class:method naming
        // See \Symfony\Component\HttpKernel\DependencyInjection\RegisterControllerArgumentLocatorsPass
        $controller = str_replace('::', ':', $controller);

        if ($this->container->has($controller)) {
            return $this->container->get($controller);
        }
    }
}
