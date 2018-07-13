<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Controller;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerNameResolver implements ControllerNameResolverInterface
{
    /**
     * @var ControllerNameDeprecationsConverter
     */
    private $deprecationsConverter;

    /**
     * {@inheritdoc}
     */
    public function setDeprecationsConverter(ControllerNameDeprecationsConverterInterface $deprecationsConverter)
    {
        $this->deprecationsConverter = $deprecationsConverter;
    }

    /**
     * @see \Symfony\Component\HttpKernel\Controller\ControllerResolver::getController For possible $controller forms
     *
     * {@inheritdoc}
     */
    public function resolve($controller)
    {
        if (is_array($controller) && isset($controller[0]) && isset($controller[1])) {

            if (is_string($controller[0])) {
                return $this->resolveClass($controller[0]).'::'.$controller[1];
            } elseif (is_object(($controller[0]))) {
                return get_class($controller[0]).'::'.$controller[1];
            }
        }

        if (is_object($controller)) {
            return get_class($controller).'::__invoke';
        }

        if (function_exists($controller)) {
            // TODO: how to deal with this case?
            return false;
        }

        if (is_string($controller)) {
            $controller = $this->convertDeprecations($controller);

            // TODO: do we need to check $controller string format here?

            list($class, $method) = explode('::', $controller);
            $class = $this->resolveClass($class);
            return $class.'::'.$method;
        }

        throw new \InvalidArgumentException('Unable to resolve controller name, the controller is not callable.');
    }

    protected function resolveClass($class)
    {
        return $class;
    }

    /**
     * @param string $controller
     *
     * @return string
     *
     * @throws \InvalidArgumentException If converting fails
     */
    private function convertDeprecations($controller)
    {
        if (!$this->deprecationsConverter) {
            return $controller;
        }

        try {
            $controller = $this->deprecationsConverter->convert($controller);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }

        return $controller;
    }
}
