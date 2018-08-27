<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerMetadataFactory
{
    /**
     * @var ArgumentMetadataFactoryInterface
     */
    private $argumentMetadataFactory;

    /**
     * ControllerMetadataFactory constructor.
     *
     * @param ArgumentMetadataFactoryInterface|null $argumentMetadataFactory
    */
    public function __construct(ArgumentMetadataFactoryInterface $argumentMetadataFactory = null)
    {
        $this->argumentMetadataFactory = $argumentMetadataFactory ?: new ArgumentMetadataFactory();
    }

    /**
     * @param string $controller
     *
     * @return ControllerMetadata
     */
    public function create($controller)
    {
        if (1 !== substr_count($controller, '::') || 2 !== substr_count($controller, ':')) {
            throw new InvalidArgumentException(sprintf('Invalid controller notation: "%s", "class::method" notation expected.', $controller));
        }

        list($class, $method) = explode('::', $controller);

        $arguments = $this->argumentMetadataFactory->createArgumentMetadata([$class, $method]);

        return new ControllerMetadata($controller, $arguments);
    }
}
