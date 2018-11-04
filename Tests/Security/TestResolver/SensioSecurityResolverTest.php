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
use Yarhon\RouteGuardBundle\Security\Test\IsGrantedTest;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactoryInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolverInterface;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Security\TestProvider\SensioSecurityProvider;
use Yarhon\RouteGuardBundle\Security\TestResolver\SensioSecurityResolver;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityResolverTest extends TestCase
{
    private $controllerArgumentResolver;

    private $requestAttributesFactory;

    private $resolver;

    public function setUp()
    {
        $this->controllerArgumentResolver = $this->createMock(ControllerArgumentResolverInterface::class);

        $this->requestAttributesFactory = $this->createMock(RequestAttributesFactoryInterface::class);

        $this->resolver = new SensioSecurityResolver($this->controllerArgumentResolver, $this->requestAttributesFactory);
    }

    public function testGetProviderClass()
    {
        $this->assertSame(SensioSecurityProvider::class, $this->resolver->getProviderClass());
    }

    public function testResolve()
    {
        $routeContext = new RouteContext('index');

        $resolved = $this->resolver->resolve(new IsGrantedTest(['ROLE_USER']), $routeContext);

        $this->assertSame([['ROLE_USER'], null], $resolved);
    }

    public function testResolveSubjectVariable()
    {
        $test = new IsGrantedTest(['ROLE_USER'],'foo');

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willReturn(5);

        $resolved = $this->resolver->resolve($test, $routeContext);

        $this->assertSame([['ROLE_USER'], 5], $resolved);
    }

    public function testResolveSubjectVariableException()
    {
        $test = new IsGrantedTest(['ROLE_USER'], 'foo');

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

        $test = new IsGrantedTest([$expression]);

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

        $test = new IsGrantedTest([$expression]);

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
        $test = new IsGrantedTest(['ROLE_USER'], 'foo');
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
        $test = new IsGrantedTest(['ROLE_USER'], 'foo');
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
