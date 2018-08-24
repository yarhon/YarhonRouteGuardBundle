<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing\RouteCollection;

use Symfony\Component\Routing\RouteCollection;
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

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
        foreach ($routeCollection as $name => $route) {
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

        return $routeCollection;
    }
}
