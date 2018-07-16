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
interface TransformerInterface
{
    /**
     * @param RouteCollection $routeCollection
     *
     * @return RouteCollection
     *
     * @throws \InvalidArgumentException If unable to resolve controller name (when ControllerNameResolver is set)
     */
    public function transform(RouteCollection $routeCollection);

    /**
     * @param ControllerNameResolverInterface $controllerNameResolver
     */
    public function setControllerNameResolver(ControllerNameResolverInterface $controllerNameResolver);

    /**
     * @param string[] $ignoredControllers
     */
    public function setIgnoredControllers(array $ignoredControllers);

    /**
     * @return string[]
     */
    public function getIgnoredRoutes();
}
