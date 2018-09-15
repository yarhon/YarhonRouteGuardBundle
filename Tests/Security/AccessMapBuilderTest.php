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
use Yarhon\RouteGuardBundle\Security\AccessMap;
use Yarhon\RouteGuardBundle\Security\TestProvider\TestProviderInterface;
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

    public function testSetTestProviders()
    {
        $provider1 = $this->createMock(TestProviderInterface::class);
        $provider2 = $this->createMock(TestProviderInterface::class);
        $providers = [$provider1, $provider2];

        $this->builder->setTestProviders($providers);

        $this->assertAttributeSame($providers, 'testProviders', $this->builder);
    }

    public function testBuild()
    {
        $this->markTestIncomplete();

        $testProvider = $this->createMock(ProviderInterface::class);
        $this->builder->addTestProvider($testProvider);
    }

    public function atestIgnoredControllers()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path2' => 'class2::method',
            '/path3' => 'extra_class::method',
            '/path4' => false,
        ]);

        $ignoredControllers = [
            'class2',
            'extra',
        ];

        $transformer = new RemoveIgnoredTransformer($ignoredControllers);
        $transformed = $transformer->transform($routeCollection);

        $expected = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path4' => false,
        ]);

        $this->assertEquals($expected, $transformed);
    }
}
