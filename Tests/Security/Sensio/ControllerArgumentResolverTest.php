<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Sensio;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentValueResolverInterface;
use Yarhon\RouteGuardBundle\Security\Sensio\ControllerArgumentResolver;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerArgumentResolverTest extends TestCase
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

        $this->resolver = new ControllerArgumentResolver($this->metadataCache, $this->requestAttributesFactory, $requestStack, $this->valueResolvers);
    }

    public function testGetArgument()
    {
        $routeContext = new RouteContext('index');

        $argumentMetadata = $this->createArgumentMetadata('arg1');
        $controllerMetadata = new ControllerMetadata('class::method', [$argumentMetadata]);
        $this->addMetadataCacheItem($routeContext->getName(), $controllerMetadata);

        $this->requestAttributesFactory->method('createAttributes')
            ->willReturn(new ParameterBag());

        $this->valueResolvers[0]->method('supports')
            ->willReturn(true);

        $this->valueResolvers[0]->method('resolve')
            ->willReturn(5);

        $value = $this->resolver->getArgument($routeContext, 'arg1');

        $this->assertEquals(5, $value);
    }

    public function testGetArgumentFromRequestAttributes()
    {
        $routeContext = new RouteContext('index');

        $controllerMetadata = new ControllerMetadata('class::method');
        $this->addMetadataCacheItem($routeContext->getName(), $controllerMetadata);

        $this->requestAttributesFactory->method('createAttributes')
            ->willReturn(new ParameterBag(['arg1' => 5]));

        $value = $this->resolver->getArgument($routeContext, 'arg1');

        $this->assertEquals(5, $value);
    }

    public function testGetArgumentException()
    {
        $routeContext = new RouteContext('index');

        $controllerMetadata = new ControllerMetadata('class::method');
        $this->addMetadataCacheItem($routeContext->getName(), $controllerMetadata);

        $this->requestAttributesFactory->method('createAttributes')
            ->willReturn(new ParameterBag(['arg2' => 5]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route "index" argument "arg1" is neither a controller argument nor request attribute.');

        $this->resolver->getArgument($routeContext, 'arg1');
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
