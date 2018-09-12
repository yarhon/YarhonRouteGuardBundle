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
use Symfony\Component\HttpFoundation\IpUtils;
use Yarhon\RouteGuardBundle\Security\Http\RequestConstraint;
use Yarhon\RouteGuardBundle\Security\Http\RequestContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestConstraintTest extends TestCase
{
    public function testConstructDefaultValues()
    {
        $constraint = new RequestConstraint();

        $this->assertEquals(null, $constraint->getPathPattern());
        $this->assertEquals(null, $constraint->getHostPattern());
        $this->assertEquals(null, $constraint->getMethods());
        $this->assertEquals(null, $constraint->getIps());
    }

    public function testConstructAllValues()
    {
        $constraint = new RequestConstraint('/path', 'host.com', ['get'], ['127.0.0.1']);

        $this->assertEquals('/path', $constraint->getPathPattern());
        $this->assertEquals('host.com', $constraint->getHostPattern());
        $this->assertEquals(['GET'], $constraint->getMethods());
        $this->assertEquals(['127.0.0.1'], $constraint->getIps());
    }

    /**
     * @dataProvider matchPathPatternDataProvider
     */
    public function testMatchPathPattern($constraintValue, $contextValue, $expected)
    {
        $constraint = new RequestConstraint($constraintValue);

        $requestContext = $this->createMock(RequestContext::class);

        $requestContext->method('getPathInfo')
            ->willReturn($contextValue);

        $this->assertSame($expected, $constraint->matches($requestContext));
    }

    public function matchPathPatternDataProvider()
    {
        return [
            [
                '/admin/.*',
                '/admin/foo',
                true,
            ],
            [
                '^/admin/.*',
                '/admin2/',
                false,
            ],
            [
                '^/admin/fo o*$',
                '/admin/fo%20o',
                true,
            ],
        ];
    }

    /**
     * @dataProvider matchHostPatternDataProvider
     */
    public function testMatchHostPattern($constraintValue, $contextValue, $expected)
    {
        $constraint = new RequestConstraint(null, $constraintValue);

        $requestContext = $this->createMock(RequestContext::class);

        $requestContext->method('getHost')
            ->willReturn($contextValue);

        $this->assertSame($expected, $constraint->matches($requestContext));
    }

    public function matchHostPatternDataProvider()
    {
        return [
            [
                '.*\.site\.com',
                '.site.com',
                true
            ],
            [
                '.*\.site\.com',
                'img.site.COM',
                true
            ],
            [
                '.*\.site\.com',
                'site.net',
                false
            ],
        ];
    }

    /**
     * @dataProvider matchMethodsDataProvider
     */
    public function testMatchMethods($constraintValue, $contextValue, $expected)
    {
        $constraint = new RequestConstraint(null, null, $constraintValue);

        $requestContext = $this->createMock(RequestContext::class);

        $requestContext->method('getMethod')
            ->willReturn($contextValue);

        $this->assertSame($expected, $constraint->matches($requestContext));
    }

    public function matchMethodsDataProvider()
    {
        return [
            [
                ['get', 'post'],
                'GET',
                true
            ],
            [
                ['GET', 'POST'],
                'GET',
                true
            ],
            [
                ['get', 'post'],
                'get',
                false
            ],
            [
                ['GET', 'POST'],
                'PUT',
                false
            ],

        ];
    }

    /**
     * @dataProvider matchIpsDataProvider
     */
    public function testMatchIps($constraintValue, $contextValue, $expected)
    {
        $constraint = new RequestConstraint(null, null, null, $constraintValue);

        $requestContext = $this->createMock(RequestContext::class);

        $requestContext->method('getClientIp')
            ->willReturn($contextValue);

        $this->assertSame($expected, $constraint->matches($requestContext));
    }

    public function matchIpsDataProvider()
    {
        return [
            [
                ['127.0.0.1', '127.0.0.2'],
                '127.0.0.1',
                true
            ],
            [
                ['127.0.0.1', '127.0.0.2'],
                '127.0.0.3',
                false
            ],
        ];
    }
}
