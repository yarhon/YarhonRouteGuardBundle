<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Routing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteContextTest extends TestCase
{
    public function testConstructDefaultValues()
    {
        $context = new RouteContext('route1');

        $this->assertSame('route1', $context->getName());
        $this->assertSame([], $context->getParameters());
        $this->assertSame('GET', $context->getMethod());
    }

    public function testConstructAllValues()
    {
        $context = new RouteContext('route1', ['q' => 1], 'POST');

        $this->assertSame('route1', $context->getName());
        $this->assertSame(['q' => 1], $context->getParameters());
        $this->assertSame('POST', $context->getMethod());
    }

    public function testReferenceType()
    {
        $context = new RouteContext('route1');
        $context->setReferenceType(UrlGeneratorInterface::ABSOLUTE_PATH);

        $this->assertEquals(UrlGeneratorInterface::ABSOLUTE_PATH, $context->getReferenceType());
    }

    public function testGeneratedUrl()
    {
        $context = new RouteContext('route1');
        $context->setGeneratedUrl('http://site.com/foo');

        $this->assertEquals('http://site.com/foo', $context->getGeneratedUrl());
    }
}
