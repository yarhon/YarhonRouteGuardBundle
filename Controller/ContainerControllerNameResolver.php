<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ContainerControllerNameResolver is responsible for resolving class name from service id
 * within controllers is service::method notation.
 * Since \Symfony\Component\DependencyInjection\ContainerInterface doesn't allow to get actual service class
 * without instantiating it, we should rely on the passed $containerClassMap that is built by ContainerClassMapPass compiler pass.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ContainerControllerNameResolver extends ControllerNameResolver
{
    /**
     * @var array
     */
    private $containerClassMap;

    /**
     * ContainerControllerNameResolver constructor.
     *
     * @param array $containerClassMap
     */
    public function __construct(array $containerClassMap)
    {
        $this->containerClassMap = $containerClassMap;
    }

    protected function resolveClass($class)
    {
        if (!isset($this->containerClassMap[$class])) {
            return $class;
        }

        $realClass = $this->containerClassMap[$class];

        if (null === $realClass) {
            throw new \InvalidArgumentException(sprintf('Unable to resolve class for service "%s".', $class));
        }

        return $realClass;
    }
}
