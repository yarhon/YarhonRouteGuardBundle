<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityArgumentResolver
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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $context;

    public function __construct(RequestAttributesFactory $requestAttributesFactory, ControllerArgumentResolver $controllerArgumentResolver, RequestStack $requestStack)
    {
        $this->requestAttributesFactory = $requestAttributesFactory;
        $this->controllerArgumentResolver = $controllerArgumentResolver;
        $this->requestStack = $requestStack;
    }

    public function createContext(RouteMetadataInterface $routeMetadata, ControllerMetadata $controllerMetadata, array $parameters)
    {
        $requestAttributes = $this->requestAttributesFactory->getAttributes($routeMetadata, $parameters);
        $argumentResolverContext = new ArgumentResolverContext($this->requestStack->getCurrentRequest(), $requestAttributes, $routeMetadata->getControllerName());

        $context = [
            'controllerMetadata' => $controllerMetadata,
            'requestAttributes' => $requestAttributes,
            'argumentResolverContext' => $argumentResolverContext,
        ];

        return $context;
    }

    /**
     * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\IsGrantedListener::onKernelControllerArguments
     * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener::onKernelControllerArguments
     */
    public function getArgument($name)
    {
        $resolved = &$this->context['resolved'];

        if (array_key_exists($name, $resolved)) {
            return $resolved[$name];
        }

        $controllerMetadata = $this->context['controllerMetadata'];
        $argumentResolverContext = $this->context['argumentResolverContext'];
        $requestAttributes = $this->context['requestAttributes'];

        if ($controllerMetadata->has($name)) {
            $argumentMetadata = $controllerMetadata->get($name);

            return $resolved[$name] = $this->controllerArgumentResolver->getArgument($argumentResolverContext, $argumentMetadata);

        } elseif ($requestAttributes->has($name)) {

            return $resolved[$name] = $requestAttributes->get($name);
        }

        throw new RuntimeException(sprintf('Cannot resolve variable "%s" - it is neither a controller argument nor request attribute.', $name));
    }

    public function getArgumentNames(RouteMetadataInterface $routeMetadata, ControllerMetadata $controllerMetadata)
    {

        $requestAttributes = $this->requestAttributesFactory->getAttributesPrototype($routeMetadata);
        // $argumentResolverContext = new ArgumentResolverContext(null, $requestAttributes, $routeMetadata->getControllerName());

        $names = [];

        foreach ($controllerMetadata as $name => $argumentMetadata) {
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
