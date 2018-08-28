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
use Yarhon\RouteGuardBundle\Exception\ExceptionInterface;

/**
 * Responsible for resolving the value of an argument based on its metadata.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
interface ArgumentValueResolverInterface
{
    /**
     * Whether this resolver can resolve the value for the given ArgumentMetadata.
     *
     * @param ArgumentResolverContextInterface $context
     * @param ArgumentMetadata                 $argument
     *
     * @return bool
     */
    public function supports(ArgumentResolverContextInterface $context, ArgumentMetadata $argument);

    /**
     * Returns the possible value(s).
     *
     * @param ArgumentResolverContextInterface $context
     * @param ArgumentMetadata                 $argument
     *
     * @return mixed
     *
     * @throws ExceptionInterface
     */
    public function resolve(ArgumentResolverContextInterface $context, ArgumentMetadata $argument);
}
