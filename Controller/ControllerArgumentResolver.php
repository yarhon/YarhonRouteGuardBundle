<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\ServiceValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * Responsible for resolving the arguments passed to an action.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * // TODO: add the controller name in thrown exceptions
 */
class ControllerArgumentResolver
{
    /**
     * @var ArgumentValueResolverInterface[]
     */
    private $argumentValueResolvers;

    /**
     * ArgumentResolver constructor.
     *
     * @param ArgumentValueResolverInterface[] $argumentValueResolvers
     */
    public function __construct($argumentValueResolvers = [])
    {
        $this->argumentValueResolvers = $argumentValueResolvers ?: self::getDefaultArgumentValueResolvers();
    }

    /**
     * @param Request            $request
     * @param ArgumentMetadata[] $argumentsMetadata
     *
     * @return array
     */
    public function getArguments(Request $request, array $argumentsMetadata)
    {
        $arguments = [];

        foreach ($argumentsMetadata as $argumentMetadata) {
            $arguments[] = $this->getArguments($request, $argumentMetadata);
        }

        return $arguments;
    }

    /**
     * @param Request            $request
     * @param ArgumentMetadata[] $argumentsMetadata
     * @param string             $argumentName
     *
     * @return mixed
     */
    public function getArgumentByName(Request $request, array $argumentsMetadata, $argumentName)
    {
        foreach ($argumentsMetadata as $argumentMetadata) {
            if ($argumentMetadata->getName() == $argumentName) {
                return $this->getArgument($request, $argumentMetadata);
            }
        }

        throw new RuntimeException(sprintf('Can\'t get metadata for argument "%s".', $argumentName));
    }


    /**
     * @param Request          $request
     * @param ArgumentMetadata $argumentMetadata
     *
     * @return mixed
     */
    public function getArgument(Request $request, ArgumentMetadata $argumentMetadata)
    {
        foreach ($this->argumentValueResolvers as $resolver) {
            if (!$resolver->supports($request, $argumentMetadata)) {
                continue;
            }

            $resolved = $resolver->resolve($request, $argumentMetadata);

            if (!$resolved instanceof \Generator) {
                throw new InvalidArgumentException(sprintf('%s::resolve() must yield at least one value.', \get_class($resolver)));
            }

            $argument = iterator_to_array($resolved, false);

            if (!$argumentMetadata->isVariadic()) {
                return $argument[0];
            } else {
                return $argument;
            }
        }

        $message = 'Argument "$%s" can\'t be resolved. Either the argument is nullable and no null value has been provided, no default value has been provided or because there is a non optional argument after this one.';
        throw new RuntimeException(sprintf($message, $argumentMetadata->getName()));
    }

    public static function getDefaultArgumentValueResolvers()
    {
        return [
            new RequestAttributeValueResolver(),
            new RequestValueResolver(),
            new SessionValueResolver(),
            // new ServiceValueResolver($container),
            new DefaultValueResolver(),
            new VariadicValueResolver(),
        ];
    }
}
