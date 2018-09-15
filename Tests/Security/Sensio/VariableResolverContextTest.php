<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Sensio;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Security\Sensio\VariableResolverContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class VariableResolverContextTest extends TestCase
{
    public function testConstruct()
    {
        $routeMetadata = $this->createMock(RouteMetadataInterface::class);
        $controllerMetadata = $this->createMock(ControllerMetadata::class);
        $requestAttributes = $this->createMock(ParameterBag::class);

        $context = new VariableResolverContext($routeMetadata, $controllerMetadata, $requestAttributes);

        $this->assertSame($routeMetadata, $context->getRouteMetadata());
        $this->assertSame($controllerMetadata, $context->getControllerMetadata());
        $this->assertSame($requestAttributes, $context->getRequestAttributes());
    }
}
