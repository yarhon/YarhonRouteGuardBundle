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
        $this->assertSame(UrlGeneratorInterface::ABSOLUTE_PATH, $context->getReferenceType());
    }

    public function testConstructAllValues()
    {
        $context = new RouteContext('route1', ['q' => 1], 'POST', UrlGeneratorInterface::RELATIVE_PATH);

        $this->assertSame('route1', $context->getName());
        $this->assertSame(['q' => 1], $context->getParameters());
        $this->assertSame('POST', $context->getMethod());
        $this->assertSame(UrlGeneratorInterface::RELATIVE_PATH, $context->getReferenceType());
    }

    public function testUrlDeferredParameters()
    {
        $context = new RouteContext('route1', ['q' => 1], 'POST', UrlGeneratorInterface::RELATIVE_PATH);

        $urlDeferred = $context->createUrlDeferred();

        $this->assertAttributeEquals('route1', 'name', $urlDeferred);
        $this->assertAttributeEquals(['q' => 1], 'parameters', $urlDeferred);
        $this->assertAttributeEquals(UrlGeneratorInterface::RELATIVE_PATH, 'referenceType', $urlDeferred);
    }

    public function testUrlDeferredSameInstance()
    {
        $context = new RouteContext('route1');

        $urlDeferred = $context->createUrlDeferred();

        $urlDeferredTwo = $context->createUrlDeferred();
        $urlDeferredThree = $context->getUrlDeferred();

        $this->assertSame($urlDeferred,$urlDeferredTwo);
        $this->assertSame($urlDeferred, $urlDeferredThree);
    }

}
