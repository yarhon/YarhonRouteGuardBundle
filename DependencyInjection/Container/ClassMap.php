<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection\Container;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ClassMap implements ClassMapInterface
{
    /**
     * @var string[]
     */
    private $map = [];

    /**
     * ClassMap constructor.
     *
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
        return isset($this->map[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->map[$id])) {
            throw new \InvalidArgumentException(sprintf('Service "%s" is not found.', $id));
        }

        return $this->map[$id];
    }
}
