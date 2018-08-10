<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Yarhon\RouteGuardBundle\Tests\HelperTrait;
use Yarhon\RouteGuardBundle\Security\AccessMapBuilder;
use Yarhon\RouteGuardBundle\Security\Provider\ProviderInterface;
use Yarhon\RouteGuardBundle\Routing\RouteCollection\TransformerInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilderTest extends TestCase
{
    use HelperTrait;

    /**
     * @var AccessMapBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->builder = new AccessMapBuilder();
    }

    public function testSetRouteCollection()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $this->builder->setRouteCollection($routeCollection);

        $this->assertAttributeEquals($routeCollection, 'routeCollection', $this->builder);
    }

    public function testImportRouteCollection()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $router = $this->createMock(RouterInterface::class);
        $router->method('getRouteCollection')
            ->willReturn($routeCollection);

        $this->builder->importRouteCollection($router);

        $this->assertAttributeEquals($routeCollection, 'routeCollection', $this->builder);
    }

    public function testSetAuthorizationProviders()
    {
        $provider1 = $this->createMock(ProviderInterface::class);
        $provider2 = $this->createMock(ProviderInterface::class);
        $providers = [$provider1, $provider2];

        $this->builder->setAuthorizationProviders($providers);

        $this->assertAttributeSame($providers, 'authorizationProviders', $this->builder);
    }

    public function testSetRouteCollectionTransformers()
    {
        $transformer1 = $this->createMock(TransformerInterface::class);
        $transformer2 = $this->createMock(TransformerInterface::class);
        $transformers = [$transformer1, $transformer2];

        $this->builder->setRouteCollectionTransformers($transformers);

        $this->assertAttributeSame($transformers, 'routeCollectionTransformers', $this->builder);
    }

    public function testTransformerCalls()
    {
        $authorizationProvider = $this->createMock(ProviderInterface::class);
        $this->builder->addAuthorizationProvider($authorizationProvider);

        $transformer = $this->createMock(TransformerInterface::class);

        $transformer->method('transform')
            ->willReturn(new RouteCollection());

        $transformer->expects($this->once())
            ->method('transform');

        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $this->builder->addRouteCollectionTransformer($transformer);
        $this->builder->setRouteCollection($routeCollection);

        $this->builder->build();

        $this->markTestIncomplete('Check transformed route collection and ignored routes.');

        $this->assertAttributeEquals(new RouteCollection(), 'routeCollection', $this->builder);
        $this->assertAttributeEquals(['/path1'], 'ignoredRoutes', $this->builder);
    }

    public function testTransformerCallException()
    {
        $authorizationProvider = $this->createMock(ProviderInterface::class);
        $this->builder->addAuthorizationProvider($authorizationProvider);

        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class',
        ]);

        $transformer = $this->createMock(TransformerInterface::class);

        $transformer->method('transform')
            ->willThrowException(new InvalidArgumentException());

        $this->builder->addRouteCollectionTransformer($transformer);
        $this->builder->setRouteCollection($routeCollection);

        $this->expectException(InvalidArgumentException::class);

        $this->builder->build();
    }

    public function testBuild()
    {
        $this->markTestIncomplete();

        $authorizationProvider = $this->createMock(ProviderInterface::class);
        $this->builder->addAuthorizationProvider($authorizationProvider);
    }
}
