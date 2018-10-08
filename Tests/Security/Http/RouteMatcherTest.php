<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Http;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Yarhon\RouteGuardBundle\Security\Http\RequestConstraint;
use Yarhon\RouteGuardBundle\Security\Http\RouteMatcher;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMatcherTest extends TestCase
{
    private $routeMatcher;

    public function setUp()
    {
        $this->routeMatcher = new RouteMatcher();
    }

    /**
     * @dataProvider matchesGeneralDataProvider
     */
    public function testMatchesGeneral($route, $constraint, $expected)
    {
        $result = $this->routeMatcher->matches($route, $constraint);

        if (is_bool($expected)) {
            $this->assertSame($expected, $result);
        } else {
            $this->assertEquals($expected, $result);
        }
    }

    public function matchesGeneralDataProvider()
    {
        return [
            [
                new Route('/blog', [], [], [], '', [], []),
                new RequestConstraint('^/blog', 'site\.com'),
                new RequestConstraint(null, 'site\.com'),
            ],
            [
                new Route('/blog', [], [], [], 'site.com', [], []),
                new RequestConstraint('^/blog', 'site\.com', ['GET']),
                new RequestConstraint(null, null, ['GET']),
            ],
            [
                new Route('/blog'),
                new RequestConstraint(null, null, null, ['127.0.0.1']),
                new RequestConstraint(null, null, null, ['127.0.0.1']),
            ],
        ];
    }

    /**
     * @dataProvider matchesByPathPatternProvider
     */
    public function testMatchesByPathPattern($routePath, $pathPattern, $expected)
    {
        $route = new Route($routePath);
        $constraint = new RequestConstraint($pathPattern);
        $result = $this->routeMatcher->matches($route, $constraint);

        if (is_bool($expected)) {
            $this->assertSame($expected, $result);
        } else {
            $expected = new RequestConstraint($expected);
            $this->assertEquals($expected, $result);
        }
    }

    public function matchesByPathPatternProvider()
    {
        return [
            // static routes
            ['/', '/', true],
            ['/', '^/', true],
            ['/blog', '/blog', true],
            ['/blog', '/admin', false],
            ['/blog', '^/blog', true],
            ['/blog', '^/admin', false],
            // dynamic routes match to "wildcard" patterns
            ['/{name}', '^/', '^/'],
            ['/blog/{author}', '^/blog', true],
            ['/blog/{author}', '^/blog.*$', true],
            ['/blog/{author}', '^/blog/', '^/blog/'],
            ['/blog/{author}', '^/blog/.*$', '^/blog/.*$'],
            // dynamic routes match to patterns without "string start" assert
            ['/blog/{author}', '/blog', true],
            ['/new/blog/{author}', '/blog', true],
            ['/new/blog/{author}', '/blog/', '/blog/'],
            ['/new/blog/{author}', '/new2', '/new2'],
            // other dynamic routes
            ['/blog/{author}', '^/blow', false],
            ['/blog/{author}', '^/blog\\d+', '^/blog\\d+'],
            ['/blog/{author}', '\\d+', '\\d+'],
            ['/blog/{author}', '^\\d+', '^\\d+'],
        ];
    }

    /**
     * @dataProvider matchesByHostPatternProvider
     */
    public function testMatchesByHostPattern($routeHost, $hostPattern, $expected)
    {
        $route = new Route('/');
        $route->setHost($routeHost);
        $constraint = new RequestConstraint(null, $hostPattern);
        $result = $this->routeMatcher->matches($route, $constraint);

        if (is_bool($expected)) {
            $this->assertSame($expected, $result);
        } else {
            $expected = new RequestConstraint(null, $expected);
            $this->assertEquals($expected, $result);
        }
    }

    public function matchesByHostPatternProvider()
    {
        return [
            ['', 'site\.com', 'site\.com'],
            ['site.com', 'ite\.com', true],
            ['site.com', '^ite\.com', false],
            ['site.com', '^site\.com', true],
            ['site.com', 'SITE\.com', true],
            ['site.com.ua', 'site\.com', true],
            ['site.com.ua', 'site\.com$', false],
            ['site.com.{country}', 'site\.com', true],
            ['site.com.{country}', '^SITE\.com', true],
            ['{country}.site.com', 'site\.com', 'site\.com'],
            ['test.{country}.site.com', 'test\.', true],
        ];
    }

    /**
     * @dataProvider matchesByMethodsProvider
     */
    public function testMatchesByMethods($routeMethods, $methods, $expected)
    {
        $route = new Route('/');
        $route->setMethods($routeMethods);
        $constraint = new RequestConstraint(null, null, $methods);
        $result = $this->routeMatcher->matches($route, $constraint);

        if (is_bool($expected)) {
            $this->assertSame($expected, $result);
        } else {
            $expected = new RequestConstraint(null, null, $expected);
            $this->assertEquals($expected, $result);
        }
    }

    public function matchesByMethodsProvider()
    {
        return [
            [[], ['GET'], ['GET']],
            [['GET', 'POST'], ['PUT'], false],
            [['GET', 'POST'], ['POST'], ['POST']],
            [['GET', 'POST'], ['GET', 'POST'], true],
            [['GET', 'POST'], ['POST', 'GET'], true],
        ];
    }

}
