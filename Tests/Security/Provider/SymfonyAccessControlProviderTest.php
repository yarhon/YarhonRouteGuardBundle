<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Security\Provider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Yarhon\LinkGuardBundle\Security\Provider\SymfonyAccessControlProvider;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\Arguments;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\TestBag;
use Yarhon\LinkGuardBundle\Security\Http\TestBagMap;
use Yarhon\LinkGuardBundle\Security\Http\RequestConstraint;
use Yarhon\LinkGuardBundle\Security\Http\RouteMatcher;
use Symfony\Component\Routing\Route;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlProviderTest extends TestCase
{
    public function testMatcher()
    {

        /*
        $route = new Route('/secure1/{page}', [], ['page' => "\d+"]);

        $constraint = new RequestConstraint('/secure1');
        $matcher = new RouteMatcher($constraint);

        // $r = $matcher->matches($route);
        */
    }
}
