<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Sensio;

use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class VariableResolverContext
{
    /**
     * @var RouteMetadataInterface
     */
    private $routeMetadata;

    /**
     * @var ControllerMetadata
     */
    private $controllerMetadata;

    /**
     * @var ParameterBag
     */
    private $requestAttributes;

    /**
     * VariableResolverContext constructor.
     *
     * @param RouteMetadataInterface $routeMetadata
     * @param ControllerMetadata     $controllerMetadata
     * @param ParameterBag           $requestAttributes
     */
    public function __construct(RouteMetadataInterface $routeMetadata, ControllerMetadata $controllerMetadata, ParameterBag $requestAttributes)
    {
        $this->routeMetadata = $routeMetadata;
        $this->controllerMetadata = $controllerMetadata;
        $this->requestAttributes = $requestAttributes;
    }

    /**
     * @return RouteMetadataInterface
     */
    public function getRouteMetadata()
    {
        return $this->routeMetadata;
    }

    /**
     * @return ControllerMetadata
     */
    public function getControllerMetadata()
    {
        return $this->controllerMetadata;
    }

    /**
     * @return ParameterBag
     */
    public function getRequestAttributes()
    {
        return $this->requestAttributes;
    }
}
