<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\TestBagInterface;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\TestBag;

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

    public function add(TestBagInterface $testBag, RequestMatcher $requestMatcher = null)
    {
        $this->map[] = [$testBag, $requestMatcher];
    }

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
