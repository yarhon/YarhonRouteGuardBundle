<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Authorization\Test;

/**
 * TestBag holds a set of authorization tests (calls of AuthorizationChecker::isGranted()).
 * Each test is represented by an Arguments instance.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBag implements TestBagInterface
{
    /**
     * @var Arguments[]
     */
    private $tests;

    /**
     * TestBag constructor.
     *
     * @param Arguments[] $tests
     */
    public function __construct(array $tests)
    {
        foreach ($tests as $test) {
            $this->add($test);
        }
    }

    public function add(Arguments $arguments)
    {
        $this->tests[] = $arguments;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->tests);
    }

    public function toArray()
    {
        return $this->tests;
    }
}
