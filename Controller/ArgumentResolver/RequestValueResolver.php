<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Returns the same instance as the request object passed along.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
final class RequestValueResolver implements ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        return Request::class === $argument->getType() || is_subclass_of($argument->getType(), Request::class);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        return $context->getRequest();
    }
}
