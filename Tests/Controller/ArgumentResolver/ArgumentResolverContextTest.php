<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Controller\ArgumentResolver;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ArgumentResolverContextTest extends TestCase
{
    public function testGetRequest()
    {
        $attributes = $this->createMock(ParameterBag::class);
        $controllerName = 'a::b';
        $request = $this->createMock(Request::class);

        $context = new ArgumentResolverContext($attributes, $controllerName, $request);

        $this->assertSame($request, $context->getRequest());

        $context = new ArgumentResolverContext($attributes, $controllerName);

        $this->assertNull($context->getRequest());
    }

    public function testGetAttributes()
    {
        $attributes = $this->createMock(ParameterBag::class);
        $controllerName = 'a::b';

        $context = new ArgumentResolverContext($attributes, $controllerName);

        $this->assertSame($attributes, $context->getAttributes());
    }

    public function testGetControllerName()
    {
        $attributes = $this->createMock(ParameterBag::class);
        $controllerName = 'a::b';

        $context = new ArgumentResolverContext($attributes, $controllerName);

        $this->assertSame($controllerName, $context->getControllerName());
    }
}
