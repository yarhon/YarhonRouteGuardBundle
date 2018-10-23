<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerNameResolver implements ControllerNameResolverInterface
{
    /**
     * @var ControllerNameConverter
     */
    private $converter;

    public function setConverter(ControllerNameConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @see \Symfony\Component\HttpKernel\Controller\ControllerResolver::getController For possible $controller forms
     *
     * {@inheritdoc}
     */
    public function resolve($controller)
    {
        if (is_array($controller) && isset($controller[0], $controller[1])) {
            if (is_string($controller[0])) {
                return $this->resolveClass($controller[0]).'::'.$controller[1];
            }

            if (is_object($controller[0])) {
                return get_class($controller[0]).'::'.$controller[1];
            }
        }

        if (is_object($controller)) {
            return get_class($controller).'::__invoke';
        }

        if (is_string($controller)) {
            if (function_exists($controller)) {
                return null;
            }

            if ($this->converter) {
                $controller = $this->converter->convert($controller);
            }

            if (false === strpos($controller, '::')) {
                return $this->resolveClass($controller).'::__invoke';
            }

            list($class, $method) = explode('::', $controller);

            return $this->resolveClass($class).'::'.$method;
        }

        throw new InvalidArgumentException('Unable to resolve controller name, the controller is not callable.');
    }

    /**
     * @param string $class
     *
     * @return string
     */
    protected function resolveClass($class)
    {
        // TODO: can controller class start from  "\" symbol ?

        return $class;
    }
}
