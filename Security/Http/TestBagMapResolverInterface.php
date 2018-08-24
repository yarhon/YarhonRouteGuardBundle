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
use Yarhon\RouteGuardBundle\Routing\UrlDeferredInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface TestBagMapResolverInterface
{
    /**
     * @param TestBagMapInterface       $testBagMap
     * @param string                    $method
     * @param UrlDeferredInterface|null $urlDeferred
     *
     * @return TestBagInterface|null
     *
     * @throws RuntimeException
     */
    public function resolve(TestBagMapInterface $testBagMap, $method = 'GET', UrlDeferredInterface $urlDeferred = null);
}
