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
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Yarhon\RouteGuardBundle\Security\Http\RequestConstraint;
use Yarhon\RouteGuardBundle\Security\Http\RouteMatcher;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMap;
use Yarhon\RouteGuardBundle\Security\Authorization\ExpressionVoter;
use Yarhon\RouteGuardBundle\Security\TestProvider\SymfonyAccessControlProvider;
use Yarhon\RouteGuardBundle\Exception\LogicException;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlProviderTest extends TestCase
{
    private $expressionLanguage;

    private $routeMatcher;

    private $provider;

    private $route;

    public function setUp()
    {
        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);

        $this->routeMatcher = $this->createMock(RouteMatcher::class);

        $this->provider = new SymfonyAccessControlProvider($this->routeMatcher);

        $this->route = new Route('/');
    }

    public function testGetTestsWithOneMatch()
    {
        $testArgumentsOne = new TestArguments(['ROLE_ADMIN']);
        $testArgumentsTwo = new TestArguments(['ROLE_USER']);

        $this->provider->addRule(new RequestConstraint(), $testArgumentsOne);
        $this->provider->addRule(new RequestConstraint(), $testArgumentsTwo);

        $this->routeMatcher->method('matches')
            ->willReturnOnConsecutiveCalls(false, true);

        $testBag = $this->provider->getTests($this->route, 'index');

        $this->assertInstanceOf(TestBag::class, $testBag);
        $testArguments = iterator_to_array($testBag)[0];

        $this->assertSame($testArguments, $testArgumentsTwo);
    }

    public function testGetTestsWithSeveralMatches()
    {
        $testArgumentsOne = new TestArguments(['ROLE_ADMIN']);
        $testArgumentsTwo = new TestArguments(['ROLE_USER']);

        $this->provider->addRule(new RequestConstraint(), $testArgumentsOne);
        $this->provider->addRule(new RequestConstraint(), $testArgumentsTwo);

        $requestConstraintForMap = new RequestConstraint();

        $this->routeMatcher->method('matches')
            ->willReturnOnConsecutiveCalls($requestConstraintForMap, true);

        $testBag = $this->provider->getTests($this->route, 'index');

        $this->assertInstanceOf(TestBagMap::class, $testBag);
        $map = iterator_to_array($testBag);

        $this->assertCount(2, $map);

        list($firstItem, $secondItem) = $map;

        $this->assertEquals($firstItem[1], $requestConstraintForMap);
        $this->assertNull($secondItem[1]);

        $this->assertSame($testArgumentsOne, iterator_to_array($firstItem[0])[0]);
        $this->assertSame($testArgumentsTwo, iterator_to_array($secondItem[0])[0]);
    }

    public function testGetTestsWithSeveralMatchesLoggerMessage()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->provider->setLogger($logger);

        $logger->expects($this->once())
            ->method('warning')
            ->with('Route with path "/" requires runtime matching to access_control rule(s) #0, #1 (zero-based), this would reduce performance.');

        $testArgumentsOne = new TestArguments(['ROLE_ADMIN']);
        $testArgumentsTwo = new TestArguments(['ROLE_USER']);

        $this->provider->addRule(new RequestConstraint(), $testArgumentsOne);
        $this->provider->addRule(new RequestConstraint(), $testArgumentsTwo);

        $requestConstraintForMap = new RequestConstraint();

        $this->routeMatcher->method('matches')
            ->willReturnOnConsecutiveCalls($requestConstraintForMap, true);

        $this->provider->getTests($this->route, 'index');
    }

    public function testGetTestsWithoutMatches()
    {
        $testBag = $this->provider->getTests($this->route, 'index');

        $this->assertNull($testBag);
    }

    public function testImportRules()
    {
        $rule = $this->createRuleArray();
        $rule['allow_if'] = null;

        $expectedConstraint = new RequestConstraint($rule['path'], $rule['host'], $rule['methods'], $rule['ips']);
        $expectedTestArguments = new TestArguments($rule['roles']);

        $this->provider->importRules([$rule]);

        $expectedRules = [[$expectedConstraint, $expectedTestArguments]];
        $this->assertAttributeEquals($expectedRules, 'rules', $this->provider);
    }

    public function testImportRulesWithExpression()
    {
        $rule = $this->createRuleArray();

        $expression = $this->createMock(Expression::class);
        $names = ExpressionVoter::getVariableNames();

        $this->expressionLanguage->expects($this->once())
            ->method('parse')
            ->with($rule['allow_if'], $names)
            ->willReturn($expression);

        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $expectedConstraint = new RequestConstraint($rule['path'], $rule['host'], $rule['methods'], $rule['ips']);
        $expectedAttributes = $rule['roles'];
        $expectedAttributes[] = $expression;
        $expectedTestArguments = new TestArguments($expectedAttributes);

        $this->provider->importRules([$rule]);

        $expectedRules = [[$expectedConstraint, $expectedTestArguments]];
        $this->assertAttributeEquals($expectedRules, 'rules', $this->provider);
    }

    public function testImportRulesWithInvalidExpressionException()
    {
        $rule = $this->createRuleArray();

        $this->expressionLanguage->method('parse')
            ->willThrowException(new SyntaxError('syntax'));

        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot parse expression "request.getClientIp() == "127.0.0.1" with following variables: "token", "user", "object", "subject", "roles", "trust_resolver", "request".');

        $this->provider->importRules([$rule]);
    }

    public function testImportRulesWithExpressionWithoutExpressionLanguage()
    {
        $rule = $this->createRuleArray();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot create expression because ExpressionLanguage is not provided.');

        $this->provider->importRules([$rule]);
    }

    private function createRuleArray()
    {
        return [
            'path' => '/foo',
            'host' => 'site.com',
            'methods' => ['GET'],
            'ips' => ['127.0.0.1'],
            'allow_if' => 'request.getClientIp() == "127.0.0.1',
            'roles' => ['ROLE_ADMIN'],
        ];
    }
}
