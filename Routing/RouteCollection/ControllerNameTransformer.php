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
use Yarhon\LinkGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerNameTransformer implements TransformerInterface
{
    /**
     * @var ControllerNameResolverInterface
     */
    private $resolver;

    /**
     * ControllerNameTransformer constructor.
     *
     * @param ControllerNameResolverInterface $resolver
     */
    public function __construct(ControllerNameResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(RouteCollection $routeCollection)
    {
        $this->resolveControllers($routeCollection);

        return $routeCollection;
    }

    /**
     * @param RouteCollection $collection
     *
     * @throws InvalidArgumentException If unable to resolve controller name
     */
    private function resolveControllers(RouteCollection $collection)
    {
        foreach ($collection as $name => $route) {
            $controller = $route->getDefault('_controller');

            try {
                // Note: for some controllers (i.e, functions as controllers), $controllerName would be false
                $controllerName = $this->resolver->resolve($controller);
                $route->setDefault('_controller', $controllerName);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(sprintf('Unable to resolve controller name for route "%s": %s',
                    $name, $e->getMessage()), 0, $e);
            }
        }
    }
}
