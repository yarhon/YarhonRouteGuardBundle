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
use Yarhon\LinkGuardBundle\Controller\ControllerNameDeprecationsConverterInterface;
use Yarhon\LinkGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerNameDeprecationsTransformer implements TransformerInterface
{
    /**
     * @var ControllerNameDeprecationsConverterInterface
     */
    private $converter;

    /**
     * ControllerNameTransformer constructor.
     *
     * @param ControllerNameDeprecationsConverterInterface $converter
     */
    public function __construct(ControllerNameDeprecationsConverterInterface $converter)
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
