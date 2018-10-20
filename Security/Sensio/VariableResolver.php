<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Sensio;

use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class VariableResolver
{
    /**
     * @var RequestAttributesFactory
     */
    private $requestAttributesFactory;

    /**
     * @var ControllerArgumentResolver
     */
    private $controllerArgumentResolver;

    /**
     * VariableResolver constructor.
     *
     * @param RequestAttributesFactory   $requestAttributesFactory
     * @param ControllerArgumentResolver $controllerArgumentResolver
     */
    public function __construct(RequestAttributesFactory $requestAttributesFactory, ControllerArgumentResolver $controllerArgumentResolver)
    {
        $this->requestAttributesFactory = $requestAttributesFactory;
        $this->controllerArgumentResolver = $controllerArgumentResolver;
    }

    /**
     * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\IsGrantedListener::onKernelControllerArguments
     * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener::onKernelControllerArguments
     *
     * @param RouteContextInterface $routeContext
     * @param string                $name
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function getVariable(RouteContextInterface $routeContext, $name)
    {
        if ($context->getControllerMetadata()->has($name)) {
            return $this->controllerArgumentResolver->getArgument($routeContext, $name);
        } elseif ($context->getRequestAttributes()->has($name)) {
            return $this->requestAttributesFactory->getAttributes($routeContext)->get($name);
        }

        throw new RuntimeException(sprintf('Variable is neither a controller argument nor request attribute.'));
    }

    /**
     * @param RouteMetadataInterface $routeMetadata
     * @param ControllerMetadata     $controllerMetadata
     *
     * @return string[]
     */
    public function getVariableNames(RouteMetadataInterface $routeMetadata, ControllerMetadata $controllerMetadata)
    {
        $requestAttributes = $this->requestAttributesFactory->getAttributesPrototype($routeMetadata);

        $names = [];

        foreach ($controllerMetadata->keys() as $name) {
            $isResolvable = true; // TODO: determine $isResolvable

            if ($isResolvable) {
                $names[] = $name;
            }
        }

        foreach ($requestAttributes->keys() as $name) {
            if (!$controllerMetadata->has($name)) {
                $names[] = $name;
            }
        }

        return $names;
    }
}
