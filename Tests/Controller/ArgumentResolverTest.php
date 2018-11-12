<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentValueResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ArgumentResolverTest extends TestCase
{
    private $metadataCache;

    private $requestAttributesFactory;

    private $request;

    private $valueResolvers;

    private $resolver;

    public function setUp()
    {
        $this->metadataCache = new ArrayAdapter(0, false);

        $this->requestAttributesFactory = $this->createMock(RequestAttributesFactory::class);

        $this->request = $this->createMock(Request::class);

        $requestStack = $this->createMock(RequestStack::class);

        $requestStack->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->valueResolvers = [
            $this->createMock(ArgumentValueResolverInterface::class),
            $this->createMock(ArgumentValueResolverInterface::class),
        ];

        $this->resolver = new ArgumentResolver($this->metadataCache, $this->requestAttributesFactory, $requestStack, $this->valueResolvers);
    }

    public function testGetArgument()
    {
        $routeContext = new RouteContext('index');

        $argumentMetadata = $this->createArgumentMetadata('arg1');
        $controllerMetadata = new ControllerMetadata('class::method', 'class', 'method', [$argumentMetadata]);
        $this->addMetadataCacheItem($routeContext->getName(), $controllerMetadata);

        $requestAttributes = new ParameterBag(['a' => 1]);

        $this->requestAttributesFactory->method('createAttributes')
            ->with($routeContext)
            ->willReturn($requestAttributes);

        $resolverContext = new ArgumentResolverContext($requestAttributes, $controllerMetadata->getName(), $this->request);

        $this->valueResolvers[0]->method('supports')
            ->willReturn(false);

        $this->valueResolvers[0]->expects($this->never())
            ->method('resolve');

        $this->valueResolvers[1]->method('supports')
            ->willReturn(true);

        $this->valueResolvers[1]->expects($this->once())
            ->method('resolve')
            ->with($resolverContext, $argumentMetadata)
            ->willReturn(5);

        $value = $this->resolver->getArgument($routeContext, 'arg1');

        $this->assertEquals(5, $value);
    }

    public function testGetArgumentNoControllerException()
    {
        $routeContext = new RouteContext('index');

        $this->addMetadataCacheItem($routeContext->getName(), null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route "index" does not have controller or controller name is unresolvable.');

        $this->resolver->getArgument($routeContext, 'arg1');
    }

    public function testGetArgumentNotExistingArgumentException()
    {
        $routeContext = new RouteContext('index');

        $this->addMetadataCacheItem($routeContext->getName(), new ControllerMetadata('class::method', 'class', 'method', []));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route "index" controller "class::method" does not have argument "$arg1".');

        $this->resolver->getArgument($routeContext, 'arg1');
    }

    public function testGetArgumentNotResolvableArgumentException()
    {
        $routeContext = new RouteContext('index');

        $argumentMetadata = $this->createArgumentMetadata('arg1');
        $controllerMetadata = new ControllerMetadata('class::method', 'class', 'method', [$argumentMetadata]);

        $this->addMetadataCacheItem($routeContext->getName(), $controllerMetadata);

        $this->requestAttributesFactory->method('createAttributes')
            ->willReturn(new ParameterBag());

        $this->valueResolvers[0]->method('supports')
            ->willReturn(false);

        $this->valueResolvers[1]->method('supports')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route "index" controller "class::method" requires that you provide a value for the "$arg1" argument.');

        $this->resolver->getArgument($routeContext, 'arg1');
    }

    public function testGetArgumentNoMetadataException()
    {
        $routeContext = new RouteContext('index');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot get ControllerMetadata for route "index".');

        $this->resolver->getArgument($routeContext, 'arg1');
    }

    public function testGetArgumentInternalCache()
    {
        $routeContext = new RouteContext('index');

        $controllerMetadata = new ControllerMetadata('class::method', 'class', 'method', [
            $this->createArgumentMetadata('arg1'),
            $this->createArgumentMetadata('arg2'),
        ]);
        $this->addMetadataCacheItem($routeContext->getName(), $controllerMetadata);

        $this->requestAttributesFactory->method('createAttributes')
            ->willReturn(new ParameterBag());

        $this->valueResolvers[0]->method('supports')
            ->willReturn(true);

        $resolvedValueOne = new \stdClass();
        $resolvedValueTwo = new \stdClass();

        $this->valueResolvers[0]->method('resolve')
            ->willReturnOnConsecutiveCalls($resolvedValueOne, $resolvedValueTwo);

        $this->valueResolvers[0]->expects($this->exactly(2))
            ->method('supports');

        $this->valueResolvers[0]->expects($this->exactly(2))
            ->method('resolve');

        $resolvedOne = $this->resolver->getArgument($routeContext, 'arg1');
        $resolvedTwo = $this->resolver->getArgument($routeContext, 'arg2');
        $resolvedThree = $this->resolver->getArgument($routeContext, 'arg1');
        $resolvedFour = $this->resolver->getArgument($routeContext, 'arg2');

        $this->assertSame($resolvedOne, $resolvedThree);
        $this->assertSame($resolvedTwo, $resolvedFour);
        $this->assertNotSame($resolvedOne, $resolvedTwo);
    }

    private function createArgumentMetadata($name)
    {
        return new ArgumentMetadata($name, 'int', false, false, null);
    }

    private function addMetadataCacheItem($name, $value)
    {
        $cacheItem = $this->metadataCache->getItem($name);
        $cacheItem->set($value);
        $this->metadataCache->save($cacheItem);
    }
}
