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
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestAttributesFactoryTest extends TestCase
{
    private $metadataCache;

    private $urlGenerator;

    private $urlGeneratorContext;

    private $factory;

    public function setUp()
    {
        $this->metadataCache = new ArrayAdapter(0, false);

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->urlGeneratorContext = $this->createMock(RequestContext::class);

        $this->urlGenerator->method('getContext')
            ->willReturn($this->urlGeneratorContext);

        $this->factory = new RequestAttributesFactory($this->metadataCache, $this->urlGenerator);
    }

    /**
     * @dataProvider createAttributesDataProvider
     */
    public function testCreateAttributes($routeMetadata, $routeContext, $generatorContext, $expected)
    {
        $this->addMetadataCacheItem($routeContext->getName(), $routeMetadata);

        $this->urlGeneratorContext->method('getParameters')
            ->willReturn($generatorContext);

        $attributes = $this->factory->createAttributes($routeContext);

        $this->assertEquals($expected, $attributes);
    }

    public function createAttributesDataProvider()
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

    public function testCreateAttributesMissingParametersException()
    {
        $routeMetadata = new RouteMetadata([], ['page']);
        $routeContext = new RouteContext('index');

        $this->addMetadataCacheItem($routeContext->getName(), $routeMetadata);

        $this->urlGeneratorContext->method('getParameters')
            ->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Some mandatory parameters are missing ("page") to get attributes for route.');

        $this->factory->createAttributes($routeContext);
    }

    public function testCreateAttributesNoMetadataException()
    {
        $routeContext = new RouteContext('index');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot get RouteMetadata for route "index".');

        $this->factory->createAttributes($routeContext);
    }

    public function testCreateAttributesInternalCache()
    {
        $routeMetadata = new RouteMetadata([], ['page']);

        $routeContextOne = new RouteContext('index', ['page' => 5]);
        $routeContextTwo = $routeContextOne;
        $routeContextThree = new RouteContext('index2', ['page' => 5]);
        $routeContextFour = $routeContextThree;

        $this->addMetadataCacheItem('index', $routeMetadata);
        $this->addMetadataCacheItem('index2', $routeMetadata);

        $this->urlGeneratorContext->method('getParameters')
            ->willReturn([]);

        $attributesOne = $this->factory->createAttributes($routeContextOne);
        $attributesTwo = $this->factory->createAttributes($routeContextTwo);
        $attributesThree = $this->factory->createAttributes($routeContextThree);
        $attributesFour = $this->factory->createAttributes($routeContextFour);

        $this->assertSame($attributesOne, $attributesTwo);
        $this->assertSame($attributesThree, $attributesFour);
        $this->assertNotSame($attributesOne, $attributesThree);
    }

    /**
     * @dataProvider getAttributeNamesDataProvider
     */
    public function testGetAttributeNames($routeMetadata, $expected)
    {
        $names = $this->factory->getAttributeNames($routeMetadata);

        $this->assertEquals($expected, $names);
    }

    public function getAttributeNamesDataProvider()
    {
        return [
            [
                new RouteMetadata([], ['page']),
                ['page'],
            ],
            [
                new RouteMetadata(['language' => 'en'], []),
                ['language'],
            ],
            [
                new RouteMetadata(['language' => 'en'], ['page']),
                ['page', 'language'],
            ],
            [
                new RouteMetadata(['language' => 'en'], ['page', 'language']),
                ['page', 'language'],
            ],
        ];
    }

    private function addMetadataCacheItem($name, $value)
    {
        $cacheItem = $this->metadataCache->getItem($name);
        $cacheItem->set($value);
        $this->metadataCache->save($cacheItem);
    }
}
