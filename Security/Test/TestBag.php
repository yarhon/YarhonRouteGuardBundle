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
 * TestBag holds a set of authorization tests. Each test is represented by a TestInterface instance.
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBag extends AbstractTestBag implements TestBagInterface
{
    /**
     * @var TestInterface[]
     */
    private $tests = [];

    /**
     * TestBag constructor.
     *
     * @param TestInterface[] $tests
     */
    public function __construct(array $tests)
    {
        foreach ($tests as $test) {
            $this->add($test);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return $this->tests;
    }

    /**
     * @param TestInterface $test
     */
    private function add(TestInterface $test)
    {
        $this->tests[] = $test;
    }
}
