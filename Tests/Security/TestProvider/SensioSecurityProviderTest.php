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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as SecurityAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted as IsGrantedAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConverterAnnotation;
use Yarhon\RouteGuardBundle\Annotations\ClassMethodAnnotationReaderInterface;
use Yarhon\RouteGuardBundle\Security\Sensio\ExpressionDecorator;
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

    private $expressionLanguage;

    private $provider;

    private $route;

    public function setUp()
    {
        $this->annotationReader = $this->createMock(ClassMethodAnnotationReaderInterface::class);

        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);

        $this->provider = new SensioSecurityProvider($this->annotationReader);

        $this->route = new Route('/');
    }

    public function testSecurityAnnotation()
    {
        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $annotation = new SecurityAnnotation(['expression' => 'request.getClientIp() == "127.0.0.1']);

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $variableNames = ['foo', 'bar'];

        $this->controllerArgumentResolver->method('getArgumentNames')
            ->willReturn($variableNames);

        $expression = $this->createMock(Expression::class);

        $namesToParse = SensioSecurityExpressionVoter::getVariableNames();

        $this->expressionLanguage->expects($this->at(0))
            ->method('parse')
            ->with($annotation->getExpression(), $namesToParse)
            ->willThrowException(new SyntaxError('syntax'));

        $namesToParse = array_merge($namesToParse, $variableNames);

        $this->expressionLanguage->expects($this->at(1))
            ->method('parse')
            ->with($annotation->getExpression(), $namesToParse)
            ->willReturn($expression);

        $testBag = $this->provider->getTests('index', $this->route, 'a::b');

        $this->assertInstanceOf(TestBag::class, $testBag);
        $testArguments = iterator_to_array($testBag)[0];
        $this->assertCount(1, $testArguments->getAttributes());

        $expressionDecorator = $testArguments->getAttributes()[0];
        $this->assertInstanceOf(ExpressionDecorator::class, $expressionDecorator);

        $this->assertSame($expression, $expressionDecorator->getExpression());
        $this->assertSame($variableNames, $expressionDecorator->getNames());
    }

    public function testSecurityAnnotationWithoutExpressionLanguageException()
    {
        $annotation = new SecurityAnnotation([]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot create expression because ExpressionLanguage is not provided.');

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->provider->getTests('index', $this->route, 'a::b');
    }

    public function testSecurityAnnotationExpressionException()
    {
        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $annotation = new SecurityAnnotation(['expression' => 'request.getClientIp() == "127.0.0.1']);

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->controllerArgumentResolver->method('getArgumentNames')
            ->willReturn(['foo', 'bar']);

        $this->expressionLanguage->method('parse')
            ->willThrowException(new SyntaxError('syntax'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot parse expression "request.getClientIp() == "127.0.0.1" with following variables: "token", "user", "object", "subject", "roles", "trust_resolver", "auth_checker", "request", "foo", "bar".');

        $this->provider->getTests('index', $this->route, 'a::b');
    }

    public function testIsGrantedAnnotation()
    {
        $annotation = new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN', 'subject' => 'foo']);

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->controllerArgumentResolver->method('getArgumentNames')
            ->willReturn(['foo']);

        $testBag = $this->provider->getTests('index', $this->route, 'a::b');

        $this->assertInstanceOf(TestBag::class, $testBag);
        $testArguments = iterator_to_array($testBag)[0];
        $this->assertCount(1, $testArguments->getAttributes());

        $attribute = $testArguments->getAttributes()[0];
        $this->assertEquals('ROLE_ADMIN', $attribute);

        $this->assertSame('foo', $testArguments->getMetadata());
    }

    public function testIsGrantedAnnotationSubjectException()
    {
        $annotation = new IsGrantedAnnotation(['attributes' => 'ROLE_ADMIN', 'subject' => 'foo']);

        $this->annotationReader->method('read')
            ->willReturn([$annotation]);

        $this->controllerArgumentResolver->method('getArgumentNames')
            ->willReturn(['bar', 'baz']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown subject variable "foo". Known variables: "bar", "baz".');

        $this->provider->getTests('index', $this->route, 'a::b');
    }

    public function testNoAnnotations()
    {
        $this->annotationReader->method('read')
            ->willReturn([]);

        $testBag = $this->provider->getTests('index', $this->route, 'a::b');

        $this->assertNull($testBag);
    }

    public function testNoControllerName()
    {
        $testBag = $this->provider->getTests('index', $this->route, null);

        $this->assertNull($testBag);
    }
}
