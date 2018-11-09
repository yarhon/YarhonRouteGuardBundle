<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\TestResolver;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\ParameterBag;
use Yarhon\RouteGuardBundle\Security\Test\SensioExtraTest;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactoryInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolverInterface;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Security\TestResolver\SensioExtraResolver;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioExtraResolverTest extends TestCase
{
    private $controllerArgumentResolver;

    private $requestAttributesFactory;

    private $resolver;

    public function setUp()
    {
        $this->controllerArgumentResolver = $this->createMock(ControllerArgumentResolverInterface::class);

        $this->requestAttributesFactory = $this->createMock(RequestAttributesFactoryInterface::class);

        $this->resolver = new SensioExtraResolver($this->controllerArgumentResolver, $this->requestAttributesFactory);
    }

    public function testSupports()
    {
        $test = new SensioExtraTest(['ROLE_USER']);

        $this->assertTrue($this->resolver->supports($test));
    }

    public function testResolve()
    {
        $routeContext = new RouteContext('index');

        $resolved = $this->resolver->resolve(new SensioExtraTest(['ROLE_USER']), $routeContext);

        $this->assertSame([['ROLE_USER'], null], $resolved);
    }

    public function testResolveSubjectVariable()
    {
        $test = new SensioExtraTest(['ROLE_USER'], 'foo');

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willReturn(5);

        $resolved = $this->resolver->resolve($test, $routeContext);

        $this->assertSame([['ROLE_USER'], 5], $resolved);
    }

    public function testResolveSubjectVariableException()
    {
        $test = new SensioExtraTest(['ROLE_USER'], 'foo');

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willThrowException(new RuntimeException('Inner exception.'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve subject variable "foo". Inner exception.');

        $this->resolver->resolve($test, $routeContext);
    }

    public function testResolveExpressionVariables()
    {
        $expression = new ExpressionDecorator(new Expression('foo == true'), ['foo']);

        $test = new SensioExtraTest([$expression]);

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willReturn(5);

        $resolved = $this->resolver->resolve($test, $routeContext);

        $this->assertSame([[$expression], null], $resolved);
        $this->assertEquals(['foo' => 5], $expression->getVariables());
    }

    public function testResolveExpressionVariablesException()
    {
        $expression = new ExpressionDecorator(new Expression('foo == true'), ['foo']);

        $test = new SensioExtraTest([$expression]);

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willThrowException(new RuntimeException('Inner exception.'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve expression variable "foo" of expression "foo == true". Inner exception.');

        $this->resolver->resolve($test, $routeContext);
    }

    public function testResolveVariableFromRequestAttributes()
    {
        $test = new SensioExtraTest(['ROLE_USER'], 'foo');
        $test->setMetadata('request_attributes', ['foo']);

        $routeContext = new RouteContext('index');

        $this->requestAttributesFactory->method('createAttributes')
            ->with($routeContext)
            ->willReturn(new ParameterBag(['foo' => 5]));

        $resolved = $this->resolver->resolve($test, $routeContext);

        $this->assertSame([['ROLE_USER'], 5], $resolved);
    }

    public function testResolveVariableFromRequestAttributesException()
    {
        $test = new SensioExtraTest(['ROLE_USER'], 'foo');
        $test->setMetadata('request_attributes', ['foo']);

        $routeContext = new RouteContext('index');

        $this->requestAttributesFactory->method('createAttributes')
            ->with($routeContext)
            ->willReturn(new ParameterBag());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve subject variable "foo" directly from Request attributes.');

        $this->resolver->resolve($test, $routeContext);
    }
}
