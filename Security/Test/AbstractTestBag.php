<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Test;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
abstract class AbstractTestBag implements AbstractTestBagInterface
{
    /**
     * @var array
     */
    protected $elements = [];

    /**
     * @var string
     */
    protected $providerClass;

    /**
     * @var mixed
     */
    protected $metadata;

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderClass($class)
    {
        $this->providerClass = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderClass()
    {
        return $this->providerClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
