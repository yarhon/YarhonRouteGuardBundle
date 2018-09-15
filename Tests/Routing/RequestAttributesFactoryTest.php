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
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestAttributesFactoryTest extends TestCase
{
    private $urlGenerator;

    private $urlGeneratorContext;

    private $routeMetadata;

    private $factory;

    public function setUp()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->urlGeneratorContext = $this->createMock(RequestContext::class);

        $this->urlGenerator->method('getContext')
            ->willReturn($this->urlGeneratorContext);

        $this->routeMetadata = $this->createMock(RouteMetadata::class);

        $this->factory = new RequestAttributesFactory($this->urlGenerator);
    }

    /**
     * @dataProvider getAttributesDataProvider
     */
    public function testGetAttributes($variables, $defaults, $context, $parameters, $expected)
    {
        $this->routeMetadata->method('getVariables')
            ->willReturn($variables);

        $this->routeMetadata->method('getDefaults')
            ->willReturn($defaults);

        $this->urlGeneratorContext->method('getParameters')
            ->willReturn($context);

        $attributes = $this->factory->getAttributes($this->routeMetadata, $parameters);

        $this->assertSame($expected, $attributes->all());
    }

    public function getAttributesDataProvider()
    {
        return [
            [
                // test default values
                [],
                ['q' => 1],
                [],
                [],
                ['q' => 1],
            ],
            [
                // test numerically indexed and null parameters are ignored
                [],
                ['page' => 1],
                [],
                [0 => 'test', 'page' => null],
                ['page' => 1],
            ],
            [
                // test context parameters and generate call parameters are ignored if not in route variable list
                ['page'],
                [],
                ['q' => 3],
                ['z' => 4, 'page' => 1],
                ['page' => 1],
            ],
            [
                // test context parameters replace defaults
                ['page'],
                ['page' => 1],
                ['page' => 3],
                [],
                ['page' => 3],
            ],
            [
                // test parameters replace defaults
                ['page'],
                ['page' => 1],
                [],
                ['page' => 3],
                ['page' => 3],
            ],
            [
                // test parameters replace context parameters
                ['page'],
                [],
                ['page' => 1],
                ['page' => 3],
                ['page' => 3],
            ],
        ];
    }

    public function testGetAttributesException()
    {
        $this->routeMetadata->method('getVariables')
            ->willReturn(['page']);

        $this->routeMetadata->method('getDefaults')
            ->willReturn([]);

        $this->urlGeneratorContext->method('getParameters')
            ->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Some mandatory parameters are missing ("page") to get attributes for route.');

        $attributes = $this->factory->getAttributes($this->routeMetadata, []);
    }

    /**
     * @dataProvider getAttributesPrototypeDataProvider
     */
    public function testGetAttributesPrototype($variables, $defaults, $expected)
    {
        $this->routeMetadata->method('getVariables')
            ->willReturn($variables);

        $this->routeMetadata->method('getDefaults')
            ->willReturn($defaults);

        $attributesPrototype = $this->factory->getAttributesPrototype($this->routeMetadata);

        $this->assertSame($expected, $attributesPrototype->keys());

    }

    public function getAttributesPrototypeDataProvider()
    {
        return [
            [
                // general test
                ['page'],
                ['offset' => 1],
                ['page', 'offset'],
            ],
        ];
    }
}
