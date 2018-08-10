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
interface RequestResolvableInterface
{
    /**
     * @param Request $request
     *
     * @return TestBagInterface|null
     */
    public function resolve(Request $request);
}
