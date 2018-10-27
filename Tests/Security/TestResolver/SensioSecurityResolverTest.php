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
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactoryInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerArgumentResolverInterface;
use Yarhon\RouteGuardBundle\Security\Sensio\ExpressionDecorator;
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
        $testArguments = [
            new TestArguments(['foo']),
            new TestArguments(['bar']),
        ];

        $testBag = $this->createTestBag($testArguments);

        $routeContext = new RouteContext('index');

        $resolved = $this->resolver->resolve($testBag, $routeContext);

        $this->assertSame($testArguments, $resolved);
    }

    public function testResolveSubjectVariable()
    {
        $testArguments = new TestArguments([]);
        $testArguments->setMetadata('subject_name', 'foo');

        $testBag = $this->createTestBag([$testArguments]);

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willReturn(5);

        $resolved = $this->resolver->resolve($testBag, $routeContext);

        $this->assertSame([$testArguments], $resolved);

        $this->assertEquals(5, $testArguments->getSubject());
    }

    public function testResolveSubjectVariableException()
    {
        $testArguments = new TestArguments([]);
        $testArguments->setMetadata('subject_name', 'foo');

        $testBag = $this->createTestBag([$testArguments]);

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willThrowException(new RuntimeException('Inner exception.'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve subject variable "foo". Inner exception.');

        $this->resolver->resolve($testBag, $routeContext);
    }

    public function testResolveExpressionVariables()
    {
        $expression = new ExpressionDecorator(new Expression('foo == true'), ['foo']);

        $testArguments = new TestArguments([$expression]);

        $testBag = $this->createTestBag([$testArguments]);

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willReturn(5);

        $resolved = $this->resolver->resolve($testBag, $routeContext);

        $this->assertSame([$testArguments], $resolved);

        $resolvedExpression = $testArguments->getAttributes()[0];

        $this->assertSame($expression, $resolvedExpression);
        $this->assertEquals(['foo' => 5], $resolvedExpression->getVariables());
    }

    public function testResolveExpressionVariablesException()
    {
        $expression = new ExpressionDecorator(new Expression('foo == true'), ['foo']);

        $testArguments = new TestArguments([$expression]);

        $testBag = $this->createTestBag([$testArguments]);

        $routeContext = new RouteContext('index');

        $this->controllerArgumentResolver->method('getArgument')
            ->with($routeContext, 'foo')
            ->willThrowException(new RuntimeException('Inner exception.'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve expression variable "foo" of expression "foo == true". Inner exception.');

        $this->resolver->resolve($testBag, $routeContext);
    }

    private function createTestBag(array $testArguments)
    {
        $testBag = $this->createMock(TestBag::class);

        $testBag->method('getIterator')
            ->willReturn(new \ArrayIterator($testArguments));

        return $testBag;
    }
}
