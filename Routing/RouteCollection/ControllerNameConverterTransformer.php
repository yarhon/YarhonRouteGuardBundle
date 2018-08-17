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
use Yarhon\RouteGuardBundle\Controller\ControllerNameConverter;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerNameConverterTransformer implements TransformerInterface
{
    /**
     * @var ControllerNameConverter
     */
    private $converter;

    /**
     * ControllerNameConverterTransformer constructor.
     *
     * @param ControllerNameConverter $converter
     */
    public function __construct(ControllerNameConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(RouteCollection $routeCollection)
    {
        $this->convertControllers($routeCollection);

        return $routeCollection;
    }

    /**
     * @param RouteCollection $collection
     *
     * @throws InvalidArgumentException If unable to resolve controller name (when ControllerNameResolver is set)
     */
    private function convertControllers(RouteCollection $collection)
    {
        foreach ($collection as $name => $route) {
            $controller = $route->getDefault('_controller');

            if (!is_string($controller)) {
                continue;
            }

            try {
                $controllerName = $this->converter->convert($controller);
                $route->setDefault('_controller', $controllerName);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException(sprintf('Unable to convert controller name for route "%s": %s',
                    $name, $e->getMessage()), 0, $e);
            }
        }
    }
}
