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
use Symfony\Component\Routing\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as SecurityAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted as IsGrantedAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConverterAnnotation;
use Symfony\Component\Security\Core\Security;
use Yarhon\RouteGuardBundle\Annotations\ClassMethodAnnotationReaderInterface;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
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
    private $annotationReader;

    private $requestAttributesFactory;

    private $expressionLanguage;

    private $provider;

    private $route;

    public function setUp()
    {
        $this->annotationReader = $this->createMock(ClassMethodAnnotationReaderInterface::class);

        $this->requestAttributesFactory = $this->createMock(RequestAttributesFactory::class);

        $routeMetadataFactory = $this->createMock(RouteMetadataFactory::class);
        $routeMetadataFactory->method('createMetadata')
            ->willReturn(new RouteMetadata([], []));

        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);

        $this->provider = new SensioSecurityProvider($this->annotationReader, $this->requestAttributesFactory, $routeMetadataFactory);

        $this->route = new Route('/');
    }

    /**
     * @dataProvider securityAnnotationDataProvider
     */
    public function testSecurityAnnotation($controllerArguments, $requestAttributes, $expected)
    {
        $allowedVariables = array_unique(array_merge($controllerArguments, $requestAttributes));

        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $annotation = new SecurityAnnotation(['expression' => 'request.isSecure']);

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->willReturn($requestAttributes);

        $controllerMetadata = $this->createControllerMetadata('class::method', $controllerArguments);

        $expression = $this->createMock(Expression::class);

        $namesToParse = SensioSecurityExpressionVoter::getVariableNames();

        $this->expressionLanguage->expects($this->at(0))
            ->method('parse')
            ->with($annotation->getExpression(), $namesToParse)
            ->willThrowException(new SyntaxError('syntax'));

        $namesToParse = array_merge($namesToParse, $allowedVariables);

        $this->expressionLanguage->expects($this->at(1))
            ->method('parse')
            ->with($annotation->getExpression(), $namesToParse)
            ->willReturn($expression);

        $testBag = $this->provider->getTests('index', $this->route, $controllerMetadata);

        $this->assertInstanceOf(TestBag::class, $testBag);
        $testArguments = iterator_to_array($testBag)[0];

        $this->assertEquals($expected, $testArguments);
    }

    public function securityAnnotationDataProvider()
    {
        $expression = $this->createMock(Expression::class);

        return [
            [
                [],
                [],
                new TestArguments([new ExpressionDecorator($expression, [])]),
            ],
            [
                ['foo'],
                ['foo'],
                new TestArguments([new ExpressionDecorator($expression, ['foo'])]),
            ],
            [
                ['foo', 'bar'],
                ['baz'],
                (new TestArguments([new ExpressionDecorator($expression, ['foo', 'bar', 'baz'])]))->setMetadata('request_attributes', ['baz']),
            ],
        ];
    }

    public function testSecurityAnnotationWithoutExpressionLanguageException()
    {
        $annotation = new SecurityAnnotation([]);

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->willReturn([]);

        $controllerMetadata = $this->createControllerMetadata('class::method', []);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot create expression because ExpressionLanguage is not provided.');

        $this->provider->getTests('index', $this->route, $controllerMetadata);
    }

    public function testSecurityAnnotationExpressionException()
    {
        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $annotation = new SecurityAnnotation(['expression' => 'request.isSecure']);

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->willReturn(['bar', 'baz']);

        $controllerMetadata = $this->createControllerMetadata('class::method', ['foo', 'bar']);

        $this->expressionLanguage->method('parse')
            ->willThrowException(new SyntaxError('syntax'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot parse expression "request.isSecure" with following variables: "token", "user", "object", "subject", "roles", "trust_resolver", "auth_checker", "request", "foo", "bar", "baz".');

        $this->provider->getTests('index', $this->route, $controllerMetadata);
    }


    /**
     * @dataProvider isGrantedAnnotationDataProvider
     */
    public function testIsGrantedAnnotation($annotation, $controllerArguments, $requestAttributes, $expected)
    {
        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->willReturn($requestAttributes);

        $controllerMetadata = $this->createControllerMetadata('class::method', $controllerArguments);

        $testBag = $this->provider->getTests('index', $this->route, $controllerMetadata);

        $this->assertInstanceOf(TestBag::class, $testBag);
        $testArguments = iterator_to_array($testBag)[0];

        $this->assertEquals($expected, $testArguments);
    }

    public function isGrantedAnnotationDataProvider()
    {
        return [
            [
                new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN']),
                ['foo', 'bar'],
                ['bar', 'baz'],
                new TestArguments(['ROLE_ADMIN']),
            ],
            [
                new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN', 'subject' => 'foo']),
                ['foo', 'bar'],
                ['bar', 'baz'],
                (new TestArguments(['ROLE_ADMIN']))->setMetadata('subject_name', 'foo'),
            ],
            [
                new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN', 'subject' => 'bar']),
                ['foo', 'bar'],
                ['bar', 'baz'],
                (new TestArguments(['ROLE_ADMIN']))->setMetadata('subject_name', 'bar'),
            ],
            [
                new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN', 'subject' => 'baz']),
                ['foo', 'bar'],
                ['bar', 'baz'],
                (new TestArguments(['ROLE_ADMIN']))->setMetadata('subject_name', 'baz')->setMetadata('request_attributes', ['baz']),
            ],
        ];
    }

    public function testIsGrantedAnnotationSubjectException()
    {
        $annotation = new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN', 'subject' => 'foo']);

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->willReturn(['baz']);

        $controllerMetadata = $this->createControllerMetadata('class::method', ['bar']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown subject variable "foo". Allowed variables: "bar", "baz');

        $this->provider->getTests('index', $this->route, $controllerMetadata);
    }

    public function testNoAnnotations()
    {
        $this->annotationReader->method('read')
            ->willReturn([]);

        $controllerMetadata = $this->createControllerMetadata('class::method', []);

        $testBag = $this->provider->getTests('index', $this->route, $controllerMetadata);

        $this->assertNull($testBag);
    }

    public function testNoControllerMetadata()
    {
        $testBag = $this->provider->getTests('index', $this->route, null);

        $this->assertNull($testBag);
    }

    /**
     * @dataProvider argumentsEqualityDataProvider
     */
    public function atestArgumentsEquality($annotationOnCallOne, $annotationOnCallTwo, $expected)
    {
        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $this->annotationReader->method('read')
            ->willReturnOnConsecutiveCalls([$annotationOnCallOne], [$annotationOnCallTwo]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->willReturn([]);

        $controllerMetadata = $this->createControllerMetadata('class::method', []);

        $expression = $this->createMock(Expression::class);

        $this->expressionLanguage->method('parse')
            ->willReturn($expression);

        $testBag = $this->provider->getTests('index', $this->route, $controllerMetadata);
        $testArgumentsOne = iterator_to_array($testBag)[0];

        $testBag = $this->provider->getTests('index', $this->route, $controllerMetadata);
        $testArgumentsTwo = iterator_to_array($testBag)[0];

        if (true === $expected) {
            $this->assertSame($testArgumentsOne, $testArgumentsTwo);
        } elseif (false === $expected) {
            $this->assertNotSame($testArgumentsOne, $testArgumentsTwo);
        }
    }

    public function argumentsEqualityDataProvider()
    {
        return [
            [
                new SecurityAnnotation(['expression' => 'request.isSecure']),
                new SecurityAnnotation(['expression' => 'not request.isSecure']),
                false,
            ],
        ];
    }

    private function createControllerMetadata($controllerName, $argumentNames)
    {
        $arguments = [];

        foreach ($argumentNames as $name) {
            $arguments[] = new ArgumentMetadata($name, 'int', false, false, null);
        }

        return new ControllerMetadata($controllerName, $arguments);
    }
}
