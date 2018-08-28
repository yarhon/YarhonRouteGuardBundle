<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Returns the Session.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
final class SessionValueResolver implements ArgumentValueResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        if (!$context->getRequest()->hasSession()) {
            return false;
        }

        $type = $argument->getType();
        if (SessionInterface::class !== $type && !is_subclass_of($type, SessionInterface::class)) {
            return false;
        }

        return $context->getRequest()->getSession() instanceof $type;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ArgumentResolverContextInterface $context, ArgumentMetadata $argument)
    {
        return $context->getRequest()->getSession();
    }
}
