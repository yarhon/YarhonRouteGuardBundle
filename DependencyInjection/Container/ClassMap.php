<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\DependencyInjection\Container;

use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * ClassMap is responsible for storing DI container class map: service id => class name.
 * Since \Symfony\Component\DependencyInjection\ContainerInterface doesn't allow to get actual service class
 * without instantiating it, we need ClassMap for the code that needs to be able to resolve service class by its id.
/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ClassMap implements ClassMapInterface
{
    /**
     * @var string[]
     */
    private $map;

    /**
     * @param array $map
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return array_key_exists($id, $this->map);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Service "%s" is not found.', $id));
        }

        return $this->map[$id];
    }
}
