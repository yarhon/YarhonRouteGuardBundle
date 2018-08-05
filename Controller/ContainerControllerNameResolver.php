<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Controller;

use Yarhon\LinkGuardBundle\DependencyInjection\Container\ClassMapInterface;
use Yarhon\LinkGuardBundle\Exception\InvalidArgumentException;

/**
 * ContainerControllerNameResolver is responsible for resolving class name from service id
 * within controllers is service::method notation.
 * Since \Symfony\Component\DependencyInjection\ContainerInterface doesn't allow to get actual service class
 * without instantiating it, we should rely on the passed ClassMapInterface $classMap.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ContainerControllerNameResolver extends ControllerNameResolver
{
    /**
     * @var ClassMapInterface
     */
    private $classMap;

    /**
     * ContainerControllerNameResolver constructor.
     *
     * @param ClassMapInterface $classMap
     */
    public function __construct(ClassMapInterface $classMap)
    {
        $this->classMap = $classMap;
    }

    /**
     * @param string $class
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function resolveClass($class)
    {
        if (!$this->classMap->has($class)) {
            return $class;
        }

        $realClass = $this->classMap->get($class);

        // Service class in container class map can be null is some cases (i.e., when service is instantiated by a factory method).
        if (null === $realClass) {
            throw new InvalidArgumentException(sprintf('Unable to resolve class for service "%s".', $class));
        }

        return $realClass;
    }
}
