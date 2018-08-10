<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Provider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Yarhon\RouteGuardBundle\Security\Provider\SymfonyAccessControlProvider;
use Yarhon\RouteGuardBundle\Security\Authorization\Test\Arguments;
use Yarhon\RouteGuardBundle\Security\Authorization\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMap;
use Yarhon\RouteGuardBundle\Security\Http\RequestConstraint;
use Yarhon\RouteGuardBundle\Security\Http\RouteMatcher;
use Symfony\Component\Routing\Route;
use Yarhon\RouteGuardBundle\Security\AccessMapBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlProviderTest extends TestCase
{
    public function testMatcher()
    {
        $this->markTestIncomplete();

        $route = new Route('/secure1/{page}', ['page' => 10], ['page' => '\d+'], ['utf8' => false]);

        $compiled = $route->compile();

        $prefix = $compiled->getStaticPrefix();

        //var_dump($prefix);

        $a = new AccessMapBuilder();

        //var_dump(isset($a[0]));

        //var_dump($compiled->getRegex());
        //$test1 = "hello";
        //var_dump(preg_match('//u', $test1));

        //$test2 = mb_convert_encoding("привет", "UTF-8",  "Windows-1251");
        //var_dump(preg_match('//u', $test2));

        //var_dump(preg_match('#^/secure1/приветі.{2}/#', '/secure1/приветії/10'));

        // $pattern = '/Component';
        // $useUtf8 = preg_match('//u', $pattern);
        // var_dump('useUtf8', $useUtf8);

        /*
        $route = new Route('/secure1/{page}', [], ['page' => "\d+"]);

        $constraint = new RequestConstraint('/secure1');
        $matcher = new RouteMatcher($constraint);

        // $r = $matcher->matches($route);
        */
    }
}
