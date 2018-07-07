<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Routing;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * Converts a short notation a:b:c to a class::method notation.
 *
 * Copied (with minimal changes) from \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser::parse
 * to not add whole symfony/framework-bundle dependency just for the one method usage.
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
     * Converts a short notation a:b:c to a class::method.
     *
     * @param string $controller A short notation controller (a:b:c)
     *
     * @return string A string in the class::method notation
     *
     * @throws \InvalidArgumentException when the specified bundle is not enabled
     *                                   or the controller cannot be found
     */
    public function convert($controller)
    {
        /*
        if (2 > func_num_args() || func_get_arg(1)) {
            @trigger_error(sprintf('The "%s" class is deprecated since Symfony 4.1.', __CLASS__), E_USER_DEPRECATED);
        }
        */

        $parts = explode(':', $controller);
        if (3 !== count($parts) || in_array('', $parts, true)) {
            throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid "a:b:c" controller string.', $controller));
        }

        $originalController = $controller;
        list($bundleName, $controller, $action) = $parts;
        $controller = str_replace('/', '\\', $controller);

        try {
            $bundle = $this->kernel->getBundle($bundleName);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf(
                'The "%s" (from the _controller value "%s") does not exist or is not enabled in your kernel!',
                $bundleName, $originalController
            ),0, $e);
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
