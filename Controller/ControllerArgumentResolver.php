<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentValueResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContextInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * Responsible for resolving the argument passed to an action.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerArgumentResolver
{
    /**
     * @var ArgumentValueResolverInterface[]
     */
    private $argumentValueResolvers;

    /**
     * ControllerArgumentResolver constructor.
     *
     * @param ArgumentValueResolverInterface[] $argumentValueResolvers
     */
    public function __construct($argumentValueResolvers = [])
    {
        $this->argumentValueResolvers = $argumentValueResolvers;
    }

    /**
     * @param ArgumentResolverContextInterface $context
     * @param ArgumentMetadata                 $argumentMetadata
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function getArgument(ArgumentResolverContextInterface $context, ArgumentMetadata $argumentMetadata)
    {
        foreach ($this->argumentValueResolvers as $resolver) {
            if (!$resolver->supports($context, $argumentMetadata)) {
                continue;
            }

            $resolved = $resolver->resolve($context, $argumentMetadata);

            return $resolved;
        }

        $message = 'Controller "%s" requires that you provide a value for the "$%s" argument. Either the argument is nullable and no null value has been provided, no default value has been provided or because there is a non optional argument after this one.';
        throw new RuntimeException(sprintf($message, $context->getControllerName(), $argumentMetadata->getName()));
    }
}
