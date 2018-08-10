<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Symfony\Component\HttpFoundation\Request;
use Yarhon\RouteGuardBundle\Security\Authorization\Test\TestBagInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagMap implements RequestResolvableInterface
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
            $this->add($item[0], $item[1]);
        }
    }

    /**
     * @param TestBagInterface    $testBag
     * @param RequestMatcher|null $requestMatcher
     */
    public function add(TestBagInterface $testBag, RequestMatcher $requestMatcher = null)
    {
        $this->map[] = [$testBag, $requestMatcher];
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request)
    {
        $resolved = null;

        foreach ($this->map as $item) {
            /** @var RequestMatcher $requestMatcher */
            list($testBag, $requestMatcher) = $item;

            if (null === $requestMatcher || $requestMatcher->matches($request)) {
                $resolved = $testBag;
                break;
            }
        }

        return $resolved;
    }
}
