<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Symfony\Component\HttpKernel\KernelInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * ControllerNameConverter holds convert methods for controller names in deprecated formats.
 * We can't just use the original code for the following reasons:
 * - convertBundleNotation: in order not to tie to symfony/framework-bundle (it's optional)
 * - convertServiceNotation: no separate converter exists in symfony/http-kernel.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @codeCoverageIgnore
 */
class ControllerNameConverter
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * ControllerNameConverter constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param mixed $controller
     *
     * @return mixed
     */
    public function convert($controller)
    {
        if (is_string($controller)) {
            $controller = $this->convertBundleNotation($controller);
            $controller = $this->convertServiceNotation($controller);
        }

        return $controller;
    }

    /**
     * @see \Symfony\Component\HttpKernel\Controller\ContainerControllerResolver::createController Original source
     *
     * @param string $controller A service:method notation controller
     *
     * @return string A string in the service::method notation
     */
    private function convertServiceNotation($controller)
    {
        if (1 === substr_count($controller, ':')) {
            $controller = str_replace(':', '::', $controller);

            // @trigger_error(sprintf('Referencing controllers with a single colon is deprecated since Symfony 4.1. Use %s instead.', $controller), E_USER_DEPRECATED);
        }

        return $controller;
    }

    /**
     * @see \Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver::createController Original source
     *
     * @throws InvalidArgumentException
     */
    private function convertBundleNotation($controller)
    {
        if (false === strpos($controller, '::') && 2 === substr_count($controller, ':')) {
            $deprecatedNotation = $controller;
            try {
                $controller = $this->parseBundleNotation($controller);
            } catch (\InvalidArgumentException $e) {
                throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
            }

            // @trigger_error(sprintf('Referencing controllers with %s is deprecated since Symfony 4.1. Use %s instead.', $deprecatedNotation, $controller), E_USER_DEPRECATED);
        }

        return $controller;
    }

    /**
     * @see \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser::parse Original source
     *
     * Converts a short notation a:b:c to a class::method.
     *
     * @param string $controller A short notation controller (a:b:c)
     *
     * @return string A string in the class::method notation
     *
     * @throws \InvalidArgumentException When the specified bundle is not enabled or the controller cannot be found
     */
    private function parseBundleNotation($controller)
    {
        $parts = explode(':', $controller);

        $originalController = $controller;
        list($bundleName, $controller, $action) = $parts;
        $controller = str_replace('/', '\\', $controller);

        try {
            $bundle = $this->kernel->getBundle($bundleName);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf(
                'The "%s" (from the _controller value "%s") does not exist or is not enabled in your kernel!',
                $bundleName, $originalController
            ), 0, $e);
        }

        $try = $bundle->getNamespace().'\\Controller\\'.$controller.'Controller';
        if (class_exists($try)) {
            return $try.'::'.$action.'Action';
        }

        throw new \InvalidArgumentException(sprintf(
            'The _controller value "%s:%s:%s" maps to a "%s" class, but this class was not found. Create this class or check the spelling of the class and its namespace.',
            $bundleName, $controller, $action, $try)
        );
    }
}
