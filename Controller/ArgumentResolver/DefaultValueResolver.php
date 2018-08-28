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
 * Returns the default value defined in the action signature when no value has been given.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
final class DefaultValueResolver implements ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        return $argument->hasDefaultValue() || (null !== $argument->getType() && $argument->isNullable() && !$argument->isVariadic());
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        return $argument->hasDefaultValue() ? $argument->getDefaultValue() : null;
    }
}
