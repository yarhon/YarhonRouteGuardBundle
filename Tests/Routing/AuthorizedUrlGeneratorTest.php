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
use Yarhon\RouteGuardBundle\Routing\AuthorizedUrlGenerator;
use Yarhon\RouteGuardBundle\Routing\LocalizedRouteDetector;
use Yarhon\RouteGuardBundle\Routing\GeneratedUrlAwareRouteContext;
use Yarhon\RouteGuardBundle\Security\RouteAuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AuthorizedUrlGeneratorTest extends TestCase
{
    private $generatorDelegate;

    private $authorizationChecker;

    private $localizedRouteDetector;

    private $generator;

    public function setUp()
    {
        $this->generatorDelegate = $this->createMock(UrlGeneratorInterface::class);
        $this->authorizationChecker = $this->createMock(RouteAuthorizationCheckerInterface::class);
        $this->localizedRouteDetector = $this->createMock(LocalizedRouteDetector::class);

        $this->generator = new AuthorizedUrlGenerator($this->generatorDelegate, $this->authorizationChecker, $this->localizedRouteDetector);
    }

    public function testGenerateRouteContext()
    {
        $arguments = ['route1', ['page' => 1], 'POST', UrlGeneratorInterface::RELATIVE_PATH];

        $expectedRouteContext = new GeneratedUrlAwareRouteContext(...array_slice($arguments, 0, 3));
        $expectedRouteContext->setReferenceType($arguments[3]);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with($expectedRouteContext);

        $this->generator->generate(...$arguments);
    }

    public function testGenerateNotAuthorized()
    {
        $this->authorizationChecker->method('isGranted')
            ->willReturn(false);

        $this->assertFalse($this->generator->generate('route1'));
    }

    public function testGenerateAuthorized()
    {
        $this->authorizationChecker->method('isGranted')
            ->willReturn(true);

        $this->generatorDelegate->expects($this->once())
            ->method('generate')
            ->with('route1', ['page' => 1], UrlGeneratorInterface::RELATIVE_PATH)
            ->willReturn('/url1');

        $this->assertEquals('/url1', $this->generator->generate('route1', ['page' => 1], 'POST', UrlGeneratorInterface::RELATIVE_PATH));
    }

    public function testGenerateWithoutRouteContextUrlGenerated()
    {
        $this->authorizationChecker->method('isGranted')
            ->willReturn(true);

        $this->generatorDelegate->expects($this->once())
            ->method('generate');

        $this->generator->generate('route1');
    }

    public function testGenerateWithRouteContextUrlGenerated()
    {
        $this->authorizationChecker->method('isGranted')
            ->willReturnCallback(function ($routeContext) {
                $routeContext->setGeneratedUrl('/generated_url');

                return true;
            });

        $this->generatorDelegate->expects($this->never())
            ->method('generate');

        $this->assertEquals('/generated_url', $this->generator->generate('route1'));
    }

    public function testLocalizedRoute()
    {
        $this->localizedRouteDetector->method('getLocalizedName')
            ->willReturn('route1.en');

        $expectedRouteContext = new GeneratedUrlAwareRouteContext('route1.en');
        $expectedRouteContext->setReferenceType(UrlGeneratorInterface::ABSOLUTE_PATH);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with($expectedRouteContext)
            ->willReturn(true);

        $this->generatorDelegate->expects($this->once())
            ->method('generate')
            ->with('route1', ['_locale' => 'en']);

        $this->generator->generate('route1', ['_locale' => 'en']);
    }

    public function testGenerateRouteNameNotStringException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Route name must be a string, array given.');

        $this->generator->generate([]);
    }
}
