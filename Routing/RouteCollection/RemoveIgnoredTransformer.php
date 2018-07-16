<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Routing\RouteCollection;

use Symfony\Component\Routing\RouteCollection;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RemoveIgnoredTransformer implements TransformerInterface
{
    /**
     * @var string[]
     */
    private $ignoredControllers = [];

    /**
     * RemoveIgnoredTransformer constructor.
     *
     * @param array $ignoredControllers
     */
    public function __construct(array $ignoredControllers)
    {
        $this->ignoredControllers = $ignoredControllers;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(RouteCollection $routeCollection)
    {
        $this->removeIgnored($routeCollection);

        return $routeCollection;
    }

    /**
     * @param RouteCollection $collection
     */
    private function removeIgnored(RouteCollection $collection)
    {
        foreach ($collection as $name => $route) {
            // Note: for some controllers (i.e, functions as controllers), $controllerName would be false
            $controllerName = $route->getDefault('_controller');

            if (false === $controllerName || !$this->isControllerIgnored($controllerName)) {
                continue;
            }

            $collection->remove($name);
        }
    }

    /**
     * @param string $controllerName
     *
     * @return bool
     */
    private function isControllerIgnored($controllerName)
    {
        list($class) = explode('::', $controllerName);

        foreach ($this->ignoredControllers as $ignored) {
            if (0 === strpos($class, $ignored)) {
                return true;
            }
        }

        return false;
    }
}
