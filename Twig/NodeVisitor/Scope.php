<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Twig\NodeVisitor;

/**
 * Modified version of \Symfony\Bridge\Twig\NodeVisitor\Scope
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Scope
{
    private $parent;
    private $data = array();
    private $left = false;

    public function __construct(self $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Opens a new child scope.
     *
     * @return self
     */
    public function enter()
    {
        return new self($this);
    }

    /**
     * Closes current scope and returns parent one.
     *
     * @return self|null
     */
    public function leave()
    {
        $this->left = true;

        return $this->parent;
    }

    /**
     * Stores data into current scope.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     *
     * @throws \LogicException
     */
    public function set($key, $value)
    {
        if ($this->left) {
            throw new \LogicException('Left scope is not mutable.');
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Tests if a data is visible from current scope.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        if (array_key_exists($key, $this->data)) {
            return true;
        }
    }

    /**
     * Returns data visible from current scope.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $default;
    }
}