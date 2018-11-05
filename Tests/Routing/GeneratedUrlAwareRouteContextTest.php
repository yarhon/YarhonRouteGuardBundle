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
use Yarhon\RouteGuardBundle\Routing\GeneratedUrlAwareRouteContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class GeneratedUrlAwareRouteContextTest extends TestCase
{
    public function testReferenceType()
    {
        $context = new GeneratedUrlAwareRouteContext('route1');
        $context->setReferenceType(UrlGeneratorInterface::ABSOLUTE_PATH);

        $this->assertEquals(UrlGeneratorInterface::ABSOLUTE_PATH, $context->getReferenceType());
    }

    public function testGeneratedUrl()
    {
        $context = new GeneratedUrlAwareRouteContext('route1');
        $context->setGeneratedUrl('http://site.com/foo');

        $this->assertEquals('http://site.com/foo', $context->getGeneratedUrl());
    }
}
