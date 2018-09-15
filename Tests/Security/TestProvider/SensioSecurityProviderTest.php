<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\TestProvider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as SecurityAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted as IsGrantedAnnotation;
use Yarhon\RouteGuardBundle\Annotations\ClassMethodAnnotationReaderInterface;
use Yarhon\RouteGuardBundle\Security\Sensio\VariableResolver;
use Yarhon\RouteGuardBundle\Security\Sensio\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Authorization\SensioSecurityExpressionVoter;
use Yarhon\RouteGuardBundle\Security\TestProvider\SensioSecurityProvider;
use Yarhon\RouteGuardBundle\Exception\LogicException;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityProviderTest extends TestCase
{
    private $reader;

    private $expressionLanguage;

    private $provider;

    private $route;

    private $argumentMetadata;

    public function setUp()
    {
        $this->reader = $this->createMock(ClassMethodAnnotationReaderInterface::class);

        $variableResolver = $this->createMock(VariableResolver::class);
        $variableResolver->method('getVariableNames')
            ->willReturn(['foo', 'bar']);

        $this->argumentMetadata = $this->createMock(ArgumentMetadata::class);
        $this->argumentMetadata->method('getName')
            ->willReturn('arg1');

        $argumentMetadataFactory = $this->createMock(ArgumentMetadataFactoryInterface::class);
        $argumentMetadataFactory->method('createArgumentMetadata')
            ->willReturn([$this->argumentMetadata]);

        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);

        $this->provider = new SensioSecurityProvider($this->reader, $variableResolver, $argumentMetadataFactory);

        $this->route = $this->createMock(Route::class);
        $compiledRoute = $this->createMock(CompiledRoute::class);

        $this->route->method('compile')
            ->willReturn($compiledRoute);
    }

    public function testSecurityAnnotationWithoutExpressionLanguageException()
    {
        $annotation = $this->createMock(SecurityAnnotation::class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot create expression because ExpressionLanguage is not provided.');

        $this->reader->method('read')
            ->willReturn([$annotation]);

        $this->provider->getTests($this->route, 'a::b');
    }

    public function testSecurityAnnotation()
    {
        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $expressionString = 'request.getClientIp() == "127.0.0.1';

        $annotation = $this->createMock(SecurityAnnotation::class);
        $annotation->method('getExpression')
            ->willReturn($expressionString);

        $this->reader->method('read')
            ->willReturn([$annotation]);

        $expression = $this->createMock(Expression::class);

        $names = SensioSecurityExpressionVoter::getVariableNames();

        $this->expressionLanguage->expects($this->at(0))
            ->method('parse')
            ->with($expressionString, $names)
            ->willThrowException(new SyntaxError('syntax'));

        $names = array_merge($names, ['foo', 'bar']);

        $this->expressionLanguage->expects($this->at(1))
            ->method('parse')
            ->with($expressionString, $names)
            ->willReturn($expression);

        $testBag = $this->provider->getTests($this->route, 'a::b');

        $this->assertInstanceOf(TestBag::class, $testBag);
        $testArguments = iterator_to_array($testBag)[0];
        $this->assertCount(1, $testArguments->getAttributes());

        $expressionDecorator = $testArguments->getAttributes()[0];
        $this->assertInstanceOf(ExpressionDecorator::class, $expressionDecorator);

        $this->assertSame($expression, $expressionDecorator->getExpression());
        $this->assertSame($names, $expressionDecorator->getNames());
    }

    public function testSecurityAnnotationExpressionException()
    {
        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $expressionString = 'request.getClientIp() == "127.0.0.1';

        $annotation = $this->createMock(SecurityAnnotation::class);
        $annotation->method('getExpression')
            ->willReturn($expressionString);

        $this->reader->method('read')
            ->willReturn([$annotation]);

        $this->expressionLanguage->method('parse')
            ->willThrowException(new SyntaxError('syntax'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot parse expression "request.getClientIp() == "127.0.0.1" with following variables: "token", "user", "object", "subject", "roles", "trust_resolver", "auth_checker", "request", "foo", "bar".');

        $this->provider->getTests($this->route, 'a::b');
    }

    public function testIsGrantedAnnotation()
    {
        $annotation = $this->createMock(IsGrantedAnnotation::class);
        $annotation->method('getAttributes')
            ->willReturn('ROLE_ADMIN');
        $annotation->method('getSubject')
            ->willReturn('foo');

        $this->reader->method('read')
            ->willReturn([$annotation]);

        $testBag = $this->provider->getTests($this->route, 'a::b');

        $this->assertInstanceOf(TestBag::class, $testBag);
        $testArguments = iterator_to_array($testBag)[0];
        $this->assertCount(1, $testArguments->getAttributes());

        $attribute = $testArguments->getAttributes()[0];
        $this->assertEquals('ROLE_ADMIN', $attribute);

        $this->assertSame('foo', $testArguments->getMetadata());
    }

    public function testIsGrantedAnnotationSubjectException()
    {
        $annotation = $this->createMock(IsGrantedAnnotation::class);
        $annotation->method('getAttributes')
            ->willReturn('ROLE_ADMIN');
        $annotation->method('getSubject')
            ->willReturn('foo2');

        $this->reader->method('read')
            ->willReturn([$annotation]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown subject variable "foo2". Known variables: "foo", "bar".');

        $this->provider->getTests($this->route, 'a::b');
    }

    public function testTestBagMetadata()
    {
        $annotation = $this->createMock(IsGrantedAnnotation::class);
        $this->reader->method('read')
            ->willReturn([$annotation]);

        $testBag = $this->provider->getTests($this->route, 'a::b');

        $this->assertInternalType('array', $testBag->getMetadata());
        $this->assertCount(2, $testBag->getMetadata());

        list($routeMetadata, $controllerMetadata) = $testBag->getMetadata();

        $this->assertInstanceOf(RouteMetadata::class, $routeMetadata);
        $this->assertInstanceOf(ControllerMetadata::class, $controllerMetadata);

        $this->assertEquals('a::b', $routeMetadata->getControllerName());

        $this->assertTrue($controllerMetadata->has('arg1'));
        $this->assertSame($this->argumentMetadata, $controllerMetadata->get('arg1'));
    }

    public function testNoAnnotations()
    {
        $this->reader->method('read')
            ->willReturn([]);

        $testBag = $this->provider->getTests($this->route, 'a::b');

        $this->assertNull($testBag);
    }
}
