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

    public function add(TestBag $testBag, RequestMatcher $requestMatcher = null)
    {
        $this->map[] = [$testBag, $requestMatcher];
    }

    public function getIterator()
    {
        $this->checkIfResolved();

        return new \ArrayIterator($this->tests);
    }

    public function toArray()
    {
        $this->checkIfResolved();

        return $this->tests;
    }

    public function resolve(Request $request)
    {
        $resolved = null;

        foreach ($this->map as $item) {
            /** @var TestBag $testBag */
            /** @var RequestMatcher $requestMatcher */
            list($testBag, $requestMatcher) = $item;

            if (null === $requestMatcher || $requestMatcher->matches($request)) {
                $resolved = $testBag;
                break;
            }
        }

        if ($resolved) {
            $this->tests = $resolved->toArray();
        } else {
            $this->tests = [];
        }
    }

    private function checkIfResolved()
    {
        if (null === $this->tests) {
            throw new \LogicException(sprintf('%s implements %s, you must call resolve() method before iterating over it.',
                __CLASS__, RequestResolvableInterface::class));
        }
    }
}