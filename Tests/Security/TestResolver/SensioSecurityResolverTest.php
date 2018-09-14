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
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\Sensio\VariableResolver;
use Yarhon\RouteGuardBundle\Security\Sensio\VariableResolverContext;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Security\Sensio\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Security\TestProvider\SensioSecurityProvider;
use Yarhon\RouteGuardBundle\Security\TestResolver\SensioSecurityResolver;
use Yarhon\RouteGuardBundle\Exception\LogicException;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityResolverTest extends TestCase
{
    private $variableResolver;

    private $variableResolverContext;

    private $resolver;

    private $testArgumentsOne;

    private $testArgumentsTwo;

    private $routeMetadata;

    private $controllerMetadata;

    private $routeContext;

    public function setUp()
    {
        $this->variableResolver = $this->createMock(VariableResolver::class);
        $this->variableResolverContext = $this->createMock(VariableResolverContext::class);

        $this->variableResolver->method('createContext')
            ->willReturn($this->variableResolverContext);

        $this->resolver = new SensioSecurityResolver($this->variableResolver);

        $this->testArgumentsOne = $this->createMock(TestArguments::class);
        $this->testArgumentsTwo = $this->createMock(TestArguments::class);

        $this->routeMetadata = $this->createMock(RouteMetadataInterface::class);
        $this->controllerMetadata = $this->createMock(ControllerMetadata::class);

        $this->routeContext = $this->createMock(RouteContextInterface::class);
        $this->routeContext->method('getParameters')
            ->willReturn([]);
    }

    public function testGetProviderClass()
    {
        $this->assertSame(SensioSecurityProvider::class, $this->resolver->getProviderClass());
    }

    public function testVariableResolverCalls()
    {
        $this->testArgumentsOne->method('getMetadata')
            ->willReturn('foo');

        $this->testArgumentsOne->method('getAttributes')
            ->willReturn([]);

        $this->testArgumentsTwo->method('getMetadata')
            ->willReturn('foo');

        $this->testArgumentsTwo->method('getAttributes')
            ->willReturn([]);

        $testBag = $this->createTestBag([$this->testArgumentsOne, $this->testArgumentsTwo]);

        $this->variableResolver->expects($this->once())
            ->method('createContext')
            ->with($this->routeMetadata, $this->controllerMetadata, $this->routeContext->getParameters());

        $this->variableResolver->expects($this->once())
            ->method('getVariable')
            ->with($this->variableResolverContext, 'foo');

        $this->resolver->resolve($testBag, $this->routeContext);
    }

    public function testResolveSubjectVariables()
    {
        $this->testArgumentsOne->method('getMetadata')
            ->willReturn('foo');

        $this->testArgumentsOne->method('getAttributes')
            ->willReturn([]);

        $testBag = $this->createTestBag([$this->testArgumentsOne]);

        $this->variableResolver->method('getVariable')
            ->willReturn(5);

        $this->testArgumentsOne->expects($this->once())
            ->method('setSubject')
            ->with(5);

        $resolved = $this->resolver->resolve($testBag, $this->routeContext);

        $this->assertSame([$this->testArgumentsOne], $resolved);
    }

    public function testResolveSubjectVariablesException()
    {
        $this->testArgumentsOne->method('getMetadata')
            ->willReturn('foo');

        $this->testArgumentsOne->method('getAttributes')
            ->willReturn([]);

        $testBag = $this->createTestBag([$this->testArgumentsOne]);

        $this->variableResolver->method('getVariable')
            ->willThrowException(new RuntimeException('inner exception'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve subject variable "foo". inner exception');

        $this->resolver->resolve($testBag, $this->routeContext);
    }

    public function testResolveExpressionVariables()
    {
        $expression = $this->createMock(ExpressionDecorator::class);
        $expression->method('getNames')
            ->willReturn(['foo']);

        $this->testArgumentsOne->method('getAttributes')
            ->willReturn([$expression]);

        $testBag = $this->createTestBag([$this->testArgumentsOne]);

        $this->variableResolver->method('getVariable')
            ->willReturn(5);

        $expression->expects($this->once())
            ->method('setVariables')
            ->with(['foo' => 5]);

        $resolved = $this->resolver->resolve($testBag, $this->routeContext);

        $this->assertSame([$this->testArgumentsOne], $resolved);
    }

    public function testResolveExpressionVariablesException()
    {
        $expression = $this->createMock(ExpressionDecorator::class);
        $expression->method('getNames')
            ->willReturn(['foo']);
        $expression->method('getExpression')
            ->willReturn('foo == true');

        $this->testArgumentsOne->method('getAttributes')
            ->willReturn([$expression]);

        $testBag = $this->createTestBag([$this->testArgumentsOne]);

        $this->variableResolver->method('getVariable')
            ->willThrowException(new RuntimeException('inner exception'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve expression variable "foo" of expression "foo == true". inner exception');

        $this->resolver->resolve($testBag, $this->routeContext);
    }

    private function createTestBag(array $testArguments)
    {
        $testBag = $this->createMock(TestBag::class);

        $testBag->method('getIterator')
            ->willReturn(new \ArrayIterator($testArguments));

        $testBag->method('getMetadata')
            ->willReturn([$this->routeMetadata, $this->controllerMetadata]);

        return $testBag;
    }
}
