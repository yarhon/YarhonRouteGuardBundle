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

/**
 * Returns a non-variadic argument's value from the request attributes.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
final class RequestAttributeValueResolver implements ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        return !$argument->isVariadic() && $context->getAttributes()->has($argument->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        return $context->getAttributes()->get($argument->getName());
    }
}
