<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Sensio;

use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver as BaseControllerArgumentResolver;
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
class ControllerArgumentResolver extends BaseControllerArgumentResolver
{
    /**
     * {@inheritdoc}
     */
    public function getArgument(RouteContextInterface $routeContext, $name)
    {
        $controllerMetadata = $this->getControllerMetadata($routeContext->getName());

        $argumentNames = $controllerMetadata->keys();

        if (!in_array($name, $argumentNames, true)) {
            $requestAttributes = $this->requestAttributesFactory->createAttributes($routeContext);

            if (!$requestAttributes->has($name)) {
                $message = 'Route "%s" argument "%s" is neither a controller argument nor request attribute.';
                throw new RuntimeException(sprintf($message, $routeContext->getName(), $name));
            }

            return $requestAttributes->get($name);
        }

        return parent::getArgument($routeContext, $name);
    }
}
