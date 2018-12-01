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
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\ExpressionLanguage\Node\Node;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as SecurityAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted as IsGrantedAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConverterAnnotation;
use Yarhon\RouteGuardBundle\Annotations\ClassMethodAnnotationReaderInterface;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionDecorator;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionAnalyzer;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Routing\RequestAttributesFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Security\Test\SensioExtraTest;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Authorization\SensioSecurityExpressionVoter;
use Yarhon\RouteGuardBundle\Security\TestProvider\SensioExtraProvider;
use Yarhon\RouteGuardBundle\Exception\LogicException;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioExtraProviderTest extends TestCase
{
    private $annotationReader;

    private $requestAttributesFactory;

    private $expressionLanguage;

    private $expressionAnalyzer;

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

        $this->expressionAnalyzer = $this->createMock(ExpressionAnalyzer::class);

        $this->provider = new SensioExtraProvider($this->annotationReader, $this->requestAttributesFactory, $routeMetadataFactory);

        $this->route = new Route('/');
    }

    /**
     * @dataProvider securityAnnotationDataProvider
     */
    public function testSecurityAnnotation($annotation, $controllerArguments, $requestAttributes, $parseOnFirstCall, $expected)
    {
        $allowedVariables = array_unique(array_merge($controllerArguments, $requestAttributes));

        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->willReturn($requestAttributes);

        $namesToParse = SensioSecurityExpressionVoter::getVariableNames();

        if ($parseOnFirstCall) {
            $this->expressionLanguage->expects($this->at(0))
                ->method('parse')
                ->with($annotation->getExpression(), $namesToParse)
                ->willReturnCallback(function ($expressionString) {
                    return new Expression($expressionString);
                });
        } else {
            $this->expressionLanguage->expects($this->at(0))
                ->method('parse')
                ->with($annotation->getExpression(), $namesToParse)
                ->willThrowException(new SyntaxError('syntax'));

            $namesToParse = array_merge($namesToParse, $allowedVariables);

            $this->expressionLanguage->expects($this->at(1))
                ->method('parse')
                ->with($annotation->getExpression(), $namesToParse)
                ->willReturnCallback(function ($expressionString) {
                    return new Expression($expressionString);
                });
        }

        $controllerMetadata = $this->createControllerMetadata('class::method', $controllerArguments);

        $testBag = $this->provider->getTests('index', $this->route, $controllerMetadata);

        $this->assertInstanceOf(TestBag::class, $testBag);
        $test = $testBag->getTests()[0];

        $this->assertEquals($expected, $test);
    }

    public function securityAnnotationDataProvider()
    {
        return [
            [
                new SecurityAnnotation(['expression' => 'request.isSecure']),
                [],
                [],
                false,
                new SensioExtraTest([new ExpressionDecorator(new Expression('request.isSecure'), [])]),
            ],
            [
                new SecurityAnnotation(['expression' => 'request.isSecure']),
                ['foo'],
                ['foo'],
                false,
                new SensioExtraTest([new ExpressionDecorator(new Expression('request.isSecure'), ['foo'])]),
            ],
            [
                new SecurityAnnotation(['expression' => 'request.isSecure']),
                ['foo', 'bar'],
                ['baz'],
                false,
                (new SensioExtraTest([new ExpressionDecorator(new Expression('request.isSecure'), ['foo', 'bar', 'baz'])]))->setMetadata('request_attributes', ['baz']),
            ],
        ];
    }

    /**
     * @dataProvider securityAnnotationWithExpressionAnalyzerDataProvider
     */
    public function testSecurityAnnotationWithExpressionAnalyzer($annotation, $allowedVariables, $usedVariables, $expected)
    {
        $this->provider->setExpressionLanguage($this->expressionLanguage);
        $this->provider->setExpressionAnalyzer($this->expressionAnalyzer);

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->willReturn([]);

        $namesToParse = SensioSecurityExpressionVoter::getVariableNames();
        $namesToParse = array_merge($namesToParse, $allowedVariables);

        $this->expressionLanguage->expects($this->once())
            ->method('parse')
            ->with($annotation->getExpression(), $namesToParse)
            ->willReturnCallback(function ($expressionString) {
                return $this->createParsedExpression($expressionString);
            });

        $this->expressionAnalyzer->method('getUsedVariables')
            ->willReturn($usedVariables);

        $controllerMetadata = $this->createControllerMetadata('class::method', $allowedVariables);

        $testBag = $this->provider->getTests('index', $this->route, $controllerMetadata);

        $this->assertInstanceOf(TestBag::class, $testBag);
        $test = $testBag->getTests()[0];

        $this->assertEquals($expected, $test);
    }

    public function securityAnnotationWithExpressionAnalyzerDataProvider()
    {
        return [
            [
                new SecurityAnnotation(['expression' => 'request.isSecure']),
                [],
                [],
                new SensioExtraTest([new ExpressionDecorator($this->createParsedExpression('request.isSecure'), [])]),
            ],
            [
                new SecurityAnnotation(['expression' => 'request.isSecure']),
                ['foo'],
                ['request', 'foo'],
                new SensioExtraTest([new ExpressionDecorator($this->createParsedExpression('request.isSecure'), ['foo'])]),
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

        $this->expressionLanguage->method('parse')
            ->willThrowException(new SyntaxError('syntax'));

        $controllerMetadata = $this->createControllerMetadata('class::method', ['foo', 'bar']);

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
        $test = $testBag->getTests()[0];

        $this->assertEquals($expected, $test);
    }

    public function isGrantedAnnotationDataProvider()
    {
        return [
            [
                new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN']),
                ['foo', 'bar'],
                ['bar', 'baz'],
                new SensioExtraTest(['ROLE_ADMIN']),
            ],
            [
                new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN', 'subject' => 'foo']),
                ['foo', 'bar'],
                ['bar', 'baz'],
                new SensioExtraTest(['ROLE_ADMIN'], 'foo'),
            ],
            [
                new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN', 'subject' => 'bar']),
                ['foo', 'bar'],
                ['bar', 'baz'],
                new SensioExtraTest(['ROLE_ADMIN'], 'bar'),
            ],
            [
                new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN', 'subject' => 'baz']),
                ['foo', 'bar'],
                ['bar', 'baz'],
                (new SensioExtraTest(['ROLE_ADMIN'], 'baz'))->setMetadata('request_attributes', ['baz']),
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
     * @dataProvider sameInstancesOfEqualTestsDataProvider
     */
    public function testSameInstancesOfEqualTests($callOne, $callTwo, $expected)
    {
        $annotations = [$callOne[0], $callTwo[0]];
        $controllerArguments = [$callOne[1], $callTwo[1]];
        $requestAttributes = [$callOne[2], $callTwo[2]];

        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $this->annotationReader->method('read')
            ->willReturnOnConsecutiveCalls([$annotations[0]], [$annotations[1]]);

        $this->requestAttributesFactory->method('getAttributeNames')
            ->willReturnOnConsecutiveCalls($requestAttributes[0], $requestAttributes[1]);

        $this->expressionLanguage->method('parse')
            ->willReturnCallback(function ($expressionString) {
                return new Expression($expressionString);
            });

        $controllerMetadata = $this->createControllerMetadata('class::method', $controllerArguments[0]);

        $testBag = $this->provider->getTests('index', $this->route, $controllerMetadata);
        $testOne = $testBag->getTests()[0];

        $controllerMetadata = $this->createControllerMetadata('class::method', $controllerArguments[1]);

        $testBag = $this->provider->getTests('index', $this->route, $controllerMetadata);
        $testTwo = $testBag->getTests()[0];

        if ($expected) {
            $this->assertSame($testOne, $testTwo);
        } else {
            $this->assertNotSame($testOne, $testTwo);
        }
    }

    public function sameInstancesOfEqualTestsDataProvider()
    {
        return [
            [
                [new SecurityAnnotation(['expression' => 'request.isSecure']), [], []],
                [new SecurityAnnotation(['expression' => 'not request.isSecure']), [], []],
                false,
            ],
            [
                [new SecurityAnnotation(['expression' => 'request.isSecure']), [], []],
                [new SecurityAnnotation(['expression' => 'request.isSecure']), [], []],
                true,
            ],
            [
                [new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN']), ['arg1'], ['attr1']],
                [new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN']), ['arg1'], ['attr1']],
                true,
            ],
            [
                [new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN']), ['arg1'], ['attr1']],
                [new IsGrantedAnnotation(['attributes' => 'ROLE_USER']), ['arg1'], ['attr1']],
                false,
            ],
            [
                [new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN', 'subject' => 'arg1']), ['arg1'], ['attr1']],
                [new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN']), ['arg1'], ['attr1']],
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

        return new ControllerMetadata($controllerName, 'class', 'method', $arguments);
    }

    private function createParsedExpression($expression)
    {
        return new ParsedExpression($expression, $this->createMock(Node::class));
    }
}
