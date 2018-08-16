<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagMap implements TestBagMapInterface
{
    /**
     * @var array
     */
    private $map;

    /**
     * TestBagMap constructor.
     *
     * @param array $map
     */
    public function __construct(array $map)
    {
        foreach ($map as $item) {
            $this->add(...$item);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(TestBagInterface $testBag, RequestContextMatcher $requestContextMatcher = null)
    {
        $this->map[] = [$testBag, $requestContextMatcher];
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->map);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(RequestContext $requestContext)
    {
        $resolved = null;

        foreach ($this->map as $item) {
            /** @var RequestContextMatcher $requestContextMatcher */
            list($testBag, $requestContextMatcher) = $item;

            if (null === $requestContextMatcher || $requestContextMatcher->matches($requestContext)) {
                $resolved = $testBag;
                break;
            }
        }

        return $resolved;
    }
}
