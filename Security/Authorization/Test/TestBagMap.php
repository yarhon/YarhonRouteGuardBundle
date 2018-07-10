<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Authorization\Test;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagMap implements TestBagInterface, RequestResolvableInterface
{
    /**
     * @var array
     */
    private $map;

    /**
     * @var Arguments[]
     */
    private $tests;

    /**
     * TestBagMap constructor.
     * @param array $map
     */
    public function __construct(array $map)
    {
        foreach ($map as $item) {
           $this->add($item[0], $item[1]);
        }
    }

    public function add(Arguments $arguments, RequestMatcher $requestMatcher = null)
    {
        $this->map[] = [$arguments, $requestMatcher];
    }

    public function getIterator()
    {
        if (null === $this->tests) {
            throw new \InvalidArgumentException('');
        }

        return new \ArrayIterator($this->tests);
    }

    /// split container and resolver into different classes???

    public function resolve(Request $request)
    {
        foreach ($this->map as $item) {

        }
    }
}
