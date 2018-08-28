<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller\ArgumentResolver;

use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * Returns a variadic argument's values from the request attributes.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
final class VariadicValueResolver implements ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        return $argument->isVariadic() && $context->getAttributes()->has($argument->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        $values = $context->getAttributes()->get($argument->getName());

        if (!\is_array($values)) {
            throw new InvalidArgumentException(sprintf('The action argument "...$%1$s" is required to be an array, the request attribute "%1$s" contains a type of "%2$s" instead.', $argument->getName(), \gettype($values)));
        }

        return $values;
    }
}
