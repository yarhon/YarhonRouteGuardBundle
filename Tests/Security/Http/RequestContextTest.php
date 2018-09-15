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
use Yarhon\RouteGuardBundle\Security\Http\RequestContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestContextTest extends TestCase
{
    public function testConstructDefaultValues()
    {
        $context = new RequestContext();

        $this->assertNull($context->getPathInfo());
        $this->assertNull($context->getHost());
        $this->assertNull($context->getMethod());
        $this->assertNull($context->getClientIp());
    }

    public function testConstructAllValues()
    {
        $context = new RequestContext('/foo', 'site.com', 'GET', '127.0.0.1');

        $this->assertEquals('/foo', $context->getPathInfo());
        $this->assertEquals('site.com', $context->getHost());
        $this->assertEquals('GET', $context->getMethod());
        $this->assertEquals('127.0.0.1', $context->getClientIp());
    }

    public function testPathInfoClosure()
    {
        $context = new RequestContext(function () { return '/foo'; });
        $this->assertEquals('/foo', $context->getPathInfo());
    }

    public function testHostClosure()
    {
        $context = new RequestContext(null, function () { return 'site.com'; });
        $this->assertEquals('site.com', $context->getHost());
    }
}
