<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouteCollection;
use Yarhon\LinkGuardBundle\Tests\HelperTrait;
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;
use Yarhon\LinkGuardBundle\Security\Provider\SymfonyAccessControlProvider;
use Yarhon\LinkGuardBundle\Routing\RouteCollection\ControllerNameTransformer;
use Yarhon\LinkGuardBundle\Exception\InvalidArgumentException;

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

        $authorizationProvider = $this->createMock(SymfonyAccessControlProvider::class);
        $this->builder->addAuthorizationProvider($authorizationProvider);
    }

    public function testSetRouteCollection()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $this->builder->setRouteCollection($routeCollection);

        $this->assertAttributeEquals($routeCollection, 'routeCollection', $this->builder);
    }

    public function testTransformerCallException()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class',
        ]);

        $transformer = $this->createMock(ControllerNameTransformer::class);

        $transformer->method('transform')
            ->willThrowException(new InvalidArgumentException());

        $this->builder->addRouteCollectionTransformer($transformer);
        $this->builder->setRouteCollection($routeCollection);

        $this->expectException(InvalidArgumentException::class);

        $this->builder->build();
    }

    public function testTransformerCalls()
    {
        $transformer = $this->createMock(ControllerNameTransformer::class);

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

        $this->assertAttributeEquals(new RouteCollection(), 'routeCollection', $this->builder);
        $this->assertAttributeEquals(['/path1'], 'ignoredRoutes', $this->builder);
    }

    public function testBuild()
    {
        $this->markTestIncomplete();
    }
}
