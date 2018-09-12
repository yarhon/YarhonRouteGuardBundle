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
 * TestBag holds a set of authorization tests (calls of AuthorizationChecker::isGranted()).
 * Each test is represented by an Arguments instance.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBag extends AbstractTestBag implements TestBagInterface
{
    /**
     * TestBag constructor.
     *
     * @param TestArguments[] $tests
     */
    public function __construct(array $tests)
    {
        foreach ($tests as $test) {
            $this->add($test);
        }
    }

    /**
     * @param TestArguments $arguments
     */
    private function add(TestArguments $arguments)
    {
        $this->elements[] = $arguments;
    }
}
