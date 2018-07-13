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
use Symfony\Component\Routing\Route;
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;
use Yarhon\LinkGuardBundle\Controller\ControllerNameResolver;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilderTest extends TestCase
{

    /**
     * @var ControllerNameResolver
     */
    private $nameResolver;

    /**
     * @var AccessMapBuilder
     */
    private $builder;

    public function asetUp()
    {
        $this->nameResolver = $this->createMock(ControllerNameResolver::class);
        $this->builder = new AccessMapBuilder(null, $this->nameResolver);
    }

    public function atestControllerNameConverterCall()
    {
        $nameConverter = $this->createMock(ControllerNameConverter::class);
        $nameConverter->method('convert')
            ->willReturnCallback(function($argument) { return 'c_'.$argument; });

        $this->configurator->setControllerNameConverter($nameConverter);

        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path2' => 'bundle:controller:action',
            '/path3' => function() { },
        ]);

        $this->router->method('getRouteCollection')
            ->willReturn($routeCollection);

        $nameConverter->expects($this->exactly(1))
            ->method('convert')
            ->with('bundle:controller:action');

        $accessMap = new AccessMap();
        $this->configurator->configure($accessMap);

        $routeCollectionConverted = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path2' => 'c_bundle:controller:action',
            '/path3' => function() { },
        ]);

        // Warning: this attribute is private
        $this->assertAttributeEquals($routeCollectionConverted, 'routeCollection', $accessMap);
    }

    public function atestControllerNameConverterException()
    {
        $this->markTestIncomplete('Watch catch block in convertCollectionControllers.');

        $nameConverter = $this->createMock(ControllerNameConverter::class);
        $nameConverter->method('convert')
            ->willThrowException(new \InvalidArgumentException());

        $this->configurator->setControllerNameConverter($nameConverter);

        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class.method',
        ]);

        $this->router->method('getRouteCollection')
            ->willReturn($routeCollection);

        $accessMap = new AccessMap();
        $this->expectException(\InvalidArgumentException::class);
        $this->configurator->configure($accessMap);
    }

    private function createRouteCollection($routes)
    {
        $routeCollection = new RouteCollection();

        foreach ($routes as $path => $controller) {
            $route = new Route($path, ['_controller' => $controller]);
            $routeCollection->add($path, $route);
        }

        return $routeCollection;
    }
}
