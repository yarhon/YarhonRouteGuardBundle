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
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestAttributesFactoryTest extends TestCase
{
    private $metadataFactory;

    private $urlGenerator;

    private $urlGeneratorContext;

    private $factory;

    public function setUp()
    {
        $this->metadataFactory = $this->createMock(RouteMetadataFactory::class);

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->urlGeneratorContext = $this->createMock(RequestContext::class);

        $this->urlGenerator->method('getContext')
            ->willReturn($this->urlGeneratorContext);

        $this->factory = new RequestAttributesFactory($this->metadataFactory, $this->urlGenerator);
    }

    /**
     * @dataProvider getAttributesDataProvider
     */
    public function testGetAttributes($routeMetadata, $routeContext, $generatorContext, $expected)
    {
        $this->metadataFactory->method('createMetadata')
            ->with($routeContext->getName())
            ->willReturn($routeMetadata);

        $this->urlGeneratorContext->method('getParameters')
            ->willReturn($generatorContext);

        $attributes = $this->factory->getAttributes($routeContext);

        $this->assertEquals($expected, $attributes);
    }

    public function getAttributesDataProvider()
    {
        return [
            [
                // test default values
                new RouteMetadata(['q' => 1], []),
                new RouteContext('index', []),
                [],
                new ParameterBag(['q' => 1]),
            ],
            [
                // test numerically indexed and null parameters are ignored
                new RouteMetadata(['page' => 1], []),
                new RouteContext('index', [0 => 'test', 'page' => null]),
                [],
                new ParameterBag(['page' => 1]),
            ],
            [
                // test context parameters and generate call parameters are ignored if not in route variable list
                new RouteMetadata([], ['page']),
                new RouteContext('index', ['z' => 4, 'page' => 1]),
                ['q' => 3],
                new ParameterBag(['page' => 1]),
            ],
            [
                // test context parameters replace defaults
                new RouteMetadata(['page' => 1], ['page']),
                new RouteContext('index', []),
                ['page' => 3],
                new ParameterBag(['page' => 3]),
            ],
            [
                // test parameters replace defaults
                new RouteMetadata(['page' => 1], ['page']),
                new RouteContext('index', ['page' => 3]),
                [],
                new ParameterBag(['page' => 3]),
            ],
            [
                // test parameters replace context parameters
                new RouteMetadata([], ['page']),
                new RouteContext('index', ['page' => 3]),
                ['page' => 1],
                new ParameterBag(['page' => 3]),
            ],
        ];
    }

    public function testGetAttributesException()
    {
        $routeMetadata = new RouteMetadata([], ['page']);
        $routeContext = new RouteContext('index', []);

        $this->metadataFactory->method('createMetadata')
            ->with($routeContext->getName())
            ->willReturn($routeMetadata);

        $this->urlGeneratorContext->method('getParameters')
            ->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Some mandatory parameters are missing ("page") to get attributes for route.');

        $this->factory->getAttributes($routeContext);
    }

    public function testGetAttributesCache()
    {
        $routeMetadata = new RouteMetadata([], ['page']);

        $routeContextOne = new RouteContext('index', ['page' => 5]);
        $routeContextTwo = $routeContextOne;
        $routeContextThree = new RouteContext('index2', ['page' => 5]);
        $routeContextFour = $routeContextThree;

        $this->metadataFactory->method('createMetadata')
            ->willReturn($routeMetadata);

        $this->urlGeneratorContext->method('getParameters')
            ->willReturn([]);

        $this->metadataFactory->expects($this->exactly(2))
            ->method('createMetadata')
            ->withConsecutive([$routeContextOne->getName()], [$routeContextThree->getName()]);

        $attributesOne = $this->factory->getAttributes($routeContextOne);
        $attributesTwo = $this->factory->getAttributes($routeContextTwo);
        $attributesThree = $this->factory->getAttributes($routeContextThree);
        $attributesFour = $this->factory->getAttributes($routeContextFour);

        $this->assertSame($attributesOne, $attributesTwo);
        $this->assertSame($attributesThree, $attributesFour);
        $this->assertNotSame($attributesOne, $attributesThree);
    }

    /**
     * @dataProvider getAttributesPrototypeDataProvider
     */
    public function atestGetAttributesPrototype($variables, $defaults, $expected)
    {
        $routeMetadata = $this->createMock(RouteMetadata::class);

        $routeMetadata->method('getVariables')
            ->willReturn($variables);

        $routeMetadata->method('getDefaults')
            ->willReturn($defaults);

        $attributesPrototype = $this->factory->getAttributesPrototype($routeMetadata);

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
