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
            $this->assertEquals($expected, $result);
        }
    }

    public function matchesByPathPatternProvider()
    {
        return [

            ['/', '/', true],
            ['/blog', '^/blog', true],
            ['/blog', '^/blog/', false],
            ['/blog/', '^/blog', true],
            ['/blog/', '^/blog/', true],
            ['/blog/', '/blog', true],
            ['/blog/', '^/blog$', false],

            ['/new/blog/', '/blog', true],
            ['/new/blog/', '^/blog', false],

            ['/{author}', '/', new RequestConstraint('/')],
            ['/blog/{author}', '^/', true],
            ['/blog/{author}', '^/blog', true],

            ['/blog/{author}', '/blog$', new RequestConstraint('/blog$')],
            ['/blog$/{author}', '/blog\$', true],
            ['/blog/{author}', '/blog$', new RequestConstraint('/blog$')],
            ['/new/blog/{author}', '^/blog', false],
            ['/new/blog/{author}', '/blog', true],
            ['/new/blog/{author}', '/blog/', new RequestConstraint('/blog/')],

            ['/blog/{author}', '^/admin', false],
            ['/blog/{author}', '/admin', new RequestConstraint('/admin')],

            ['/blog/{author}', '^/blog/', false],
        ];
    }

    public function testAS()
    {
        $route = new Route('/blog/');
        $p = $route->compile()->getStaticPrefix();
        var_dump($p);
    }
}
