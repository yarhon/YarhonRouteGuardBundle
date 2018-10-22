<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Sensio;

use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolverInterface;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactoryInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * Sensio FrameworkExtraBundle allows to use Request attributes, in addition to the controller arguments, as variables
 * in "@Security" annotation expressions and in "@IsGranted" annotation "subject" arguments.
 * This ControllerArgumentResolver allows to fallback to the Request attribute, if controller doesn't have requested argument.
 *
 * @see \Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter::getControllerArguments
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerArgumentResolver implements ControllerArgumentResolverInterface
{
    /**
     * @var ControllerArgumentResolverInterface
     */
    private $delegate;

    /**
     * @var RequestAttributesFactoryInterface
     */
    private $requestAttributesFactory;

    /**
     * VariableResolver constructor.
     *
     * @param ControllerArgumentResolverInterface $controllerArgumentResolver
     * @param RequestAttributesFactoryInterface   $requestAttributesFactory
     */
    public function __construct(ControllerArgumentResolverInterface $controllerArgumentResolver, RequestAttributesFactoryInterface $requestAttributesFactory)
    {
        $this->delegate = $controllerArgumentResolver;
        $this->requestAttributesFactory = $requestAttributesFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getArgument(RouteContextInterface $routeContext, $name)
    {
        if (!in_array($name, $this->delegate->getArgumentNames($routeContext->getName()), true)) {
            if (!in_array($name, $this->requestAttributesFactory->getAttributeNames($routeContext->getName()), true)) {
                $message = 'Route "%s" argument "%s" is neither a controller argument nor request attribute.';
                throw new RuntimeException(sprintf($message, $routeContext->getName(), $name));
            }

            return $this->requestAttributesFactory->createAttributes($routeContext)->get($name);
        }

        return $this->delegate->getArgument($routeContext, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getArgumentNames($routeName)
    {
        $controllerArguments = $this->delegate->getArgumentNames($routeName);
        $requestAttributes = $this->requestAttributesFactory->getAttributeNames($routeName);

        return array_values(array_unique(array_merge($controllerArguments, $requestAttributes)));
    }
}
