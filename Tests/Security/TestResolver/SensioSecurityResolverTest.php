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
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
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
        $tests = [
            new IsGrantedTest(['foo']),
            new IsGrantedTest(['bar']),
        ];

        $testBag = $this->createTestBag($tests);

        $routeContext = new RouteContext('index');

        $generator = $this->resolver->resolve($testBag, $routeContext);

        $resolved = $this->processGenerator($generator);

        $this->assertSame($tests, $resolved);
    }

    public function testResolveSubjectVariable()
    {
        $test = new IsGrantedTest([]);
        $test->setMetadata('subject_name', 'foo');

        $testBag = $this->createTestBag([$test]);

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willReturn(5);

        $generator = $this->resolver->resolve($testBag, $routeContext);

        $resolved = $this->processGenerator($generator);

        $this->assertSame([$test], $resolved);

        $this->assertEquals(5, $test->getSubject());
    }

    public function testResolveSubjectVariableException()
    {
        $test = new IsGrantedTest([]);
        $test->setMetadata('subject_name', 'foo');

        $testBag = $this->createTestBag([$test]);

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willThrowException(new RuntimeException('Inner exception.'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve subject variable "foo". Inner exception.');

        $generator = $this->resolver->resolve($testBag, $routeContext);

        $this->processGenerator($generator);
    }

    public function testResolveExpressionVariables()
    {
        $expression = new ExpressionDecorator(new Expression('foo == true'), ['foo']);

        $test = new IsGrantedTest([$expression]);

        $testBag = $this->createTestBag([$test]);

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willReturn(5);

        $generator = $this->resolver->resolve($testBag, $routeContext);

        $resolved = $this->processGenerator($generator);

        $this->assertSame([$test], $resolved);

        $resolvedExpression = $test->getAttributes()[0];

        $this->assertSame($expression, $resolvedExpression);
        $this->assertEquals(['foo' => 5], $resolvedExpression->getVariables());
    }

    public function testResolveExpressionVariablesException()
    {
        $expression = new ExpressionDecorator(new Expression('foo == true'), ['foo']);

        $test = new IsGrantedTest([$expression]);

        $testBag = $this->createTestBag([$test]);

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willThrowException(new RuntimeException('Inner exception.'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve expression variable "foo" of expression "foo == true". Inner exception.');

        $generator = $this->resolver->resolve($testBag, $routeContext);

        $this->processGenerator($generator);
    }

    public function testResolveVariableFromRequestAttributes()
    {
        $test = new IsGrantedTest([]);
        $test->setMetadata('subject_name', 'foo');
        $test->setMetadata('request_attributes', ['foo']);

        $testBag = $this->createTestBag([$test]);

        $routeContext = new RouteContext('index');

        $this->requestAttributesFactory->method('createAttributes')
            ->with($routeContext)
            ->willReturn(new ParameterBag(['foo' => 5]));

        $generator = $this->resolver->resolve($testBag, $routeContext);

        $resolved = $this->processGenerator($generator);

        $this->assertSame([$test], $resolved);

        $this->assertEquals(5, $test->getSubject());
    }

    public function testResolveVariableFromRequestAttributesException()
    {
        $test = new IsGrantedTest([]);
        $test->setMetadata('subject_name', 'foo');
        $test->setMetadata('request_attributes', ['foo']);

        $testBag = $this->createTestBag([$test]);

        $routeContext = new RouteContext('index');

        $this->requestAttributesFactory->method('createAttributes')
            ->with($routeContext)
            ->willReturn(new ParameterBag());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve subject variable "foo" directly from Request attributes.');

        $generator = $this->resolver->resolve($testBag, $routeContext);

        $this->processGenerator($generator);
    }

    private function createTestBag(array $tests)
    {
        return new TestBag($tests);
    }

    private function processGenerator($generator)
    {
        $this->assertInstanceOf(\Generator::class, $generator);

        return iterator_to_array($generator);
    }
}