<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Twig\RoutingRuntime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Routing\AuthorizedUrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RoutingRuntimeTest extends TestCase
{
    private $generator;

    private $runtime;

    public function setUp()
    {
        $this->generator = $this->createMock(AuthorizedUrlGeneratorInterface::class);

        $this->runtime = new RoutingRuntime($this->generator);
    }

    /**
     * @dataProvider urlDataProvider
     */
    public function testUrl($arguments, $expectedReferenceType)
    {
        $expectedArguments = array_slice($arguments, 0, 3);
        $expectedArguments += [1 => [], 2 => 'GET', 3 => $expectedReferenceType];

        $this->generator->expects($this->once())
            ->method('generate')
            ->with(...$expectedArguments)
            ->willReturn('/url1');

        $this->assertEquals('/url1', $this->runtime->url(...$arguments));
    }

    public function urlDataProvider()
    {
        return [
            [
                ['route1', ['page' => 1], 'POST'],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ],

            [
                ['route1', ['page' => 1], 'POST', true],
                UrlGeneratorInterface::NETWORK_PATH,
            ],
        ];
    }

    /**
     * @dataProvider pathDataProvider
     */
    public function testPath($arguments, $expectedReferenceType)
    {
        $expectedArguments = array_slice($arguments, 0, 3);
        $expectedArguments += [1 => [], 2 => 'GET', 3 => $expectedReferenceType];

        $this->generator->expects($this->once())
            ->method('generate')
            ->with(...$expectedArguments)
            ->willReturn('/url1');

        $this->assertEquals('/url1', $this->runtime->path(...$arguments));
    }

    public function pathDataProvider()
    {
        return [
            [
                ['route1', ['page' => 1], 'POST'],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            ],

            [
                ['route1', ['page' => 1], 'POST', true],
                UrlGeneratorInterface::RELATIVE_PATH,
            ],
        ];
    }

    /**
     * @dataProvider routeDataProvider
     */
    public function testRoute($arguments, $expectedReferenceType)
    {
        $expectedArguments = array_slice($arguments, 0, 3);
        $expectedArguments += [1 => [], 2 => 'GET', 3 => $expectedReferenceType];

        $this->generator->expects($this->once())
            ->method('generate')
            ->with(...$expectedArguments)
            ->willReturn('/url1');

        $this->assertEquals('/url1', $this->runtime->route(...$arguments));
    }

    public function routeDataProvider()
    {
        return [
            [
                ['route1', ['page' => 1], 'POST'],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            ],

            [
                ['route1', ['page' => 1], 'POST', ['path']],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            ],

            [
                ['route1', ['page' => 1], 'POST', ['path', false]],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            ],

            [
                ['route1', ['page' => 1], 'POST', ['path', true]],
                UrlGeneratorInterface::RELATIVE_PATH,
            ],

            [
                ['route1', ['page' => 1], 'POST', ['url']],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ],

            [
                ['route1', ['page' => 1], 'POST', ['url', false]],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ],

            [
                ['route1', ['page' => 1], 'POST', ['url', true]],
                UrlGeneratorInterface::NETWORK_PATH,
            ],

        ];
    }

    public function testRouteException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid reference type: "qwerty"');

        $this->runtime->route('route1', ['page' => 1], 'POST', ['qwerty']);
    }


}
