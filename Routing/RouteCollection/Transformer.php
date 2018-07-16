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
use Yarhon\LinkGuardBundle\Controller\ControllerNameResolverInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Transformer implements TransformerInterface
{
    /**
     * @var ControllerNameResolverInterface
     */
    private $controllerNameResolver;

    /**
     * @var string[]
     */
    private $ignoredControllers = [];

    /**
     * @var string[]
     */
    private $ignoredRoutes = [];

    /**
     * {@inheritdoc}
     */
    public function transform(RouteCollection $routeCollection)
    {
        $routeCollection = clone $routeCollection;
        $this->resolveControllers($routeCollection);
        self::checkControllersFormat($routeCollection);
        $this->ignoredRoutes = $this->removeIgnored($routeCollection);

        return $routeCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerNameResolver(ControllerNameResolverInterface $controllerNameResolver)
    {
        $this->controllerNameResolver = $controllerNameResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function setIgnoredControllers(array $ignoredControllers)
    {
        $this->ignoredControllers = $ignoredControllers;
    }

    /**
     * {@inheritdoc}
     */
    public function getIgnoredRoutes()
    {
        return $this->ignoredRoutes;
    }

    /**
     * @param RouteCollection $collection
     *
     * @throws \InvalidArgumentException If controller name is not a string in the class::method notation or boolean false
     */
    public static function checkControllersFormat(RouteCollection $collection)
    {
        // TODO: should this method reside in this class?

        foreach ($collection as $name => $route) {
            $controller = $route->getDefault('_controller');

            if (false === $controller) {
                continue;
            }

            if (is_string($controller)) {
                $parts = explode('::', $controller);
                if (2 == count($parts) && !in_array('', $parts, true)) {
                    continue;
                }
            }

            throw new \InvalidArgumentException(
                sprintf('Invalid controller name for route "%s" - it should be either string in the class::method notation or boolean false.', $name)
            );
        }
    }

    /**
     * @param RouteCollection $collection
     *
     * @throws \InvalidArgumentException If unable to resolve controller name (when ControllerNameResolver is set)
     */
    private function resolveControllers(RouteCollection $collection)
    {
        if (!$this->controllerNameResolver) {
            return;
        }

        foreach ($collection as $name => $route) {
            $controller = $route->getDefault('_controller');

            try {
                // Note: for some controllers (i.e, functions as controllers), $controllerName would be false
                $controllerName = $this->controllerNameResolver->resolve($controller);
                $route->setDefault('_controller', $controllerName);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(sprintf('Unable to resolve controller name for route "%s": %s',
                    $name, $e->getMessage()), 0, $e);
            }
        }
    }

    /**
     * @param RouteCollection $collection
     *
     * @return string[] Ignored routes names
     */
    private function removeIgnored(RouteCollection $collection)
    {
        $ignored = [];

        foreach ($collection as $name => $route) {
            // Note: for some controllers (i.e, functions as controllers), $controllerName would be false
            $controllerName = $route->getDefault('_controller');

            if (false === $controllerName || !$this->isControllerIgnored($controllerName)) {
                continue;
            }

            $collection->remove($name);
            $ignored[] = $name;
        }

        return $ignored;
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
