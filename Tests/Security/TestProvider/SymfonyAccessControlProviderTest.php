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

    /**
     * @dataProvider getTestsDataProvider
     */
    public function testGetTests($testArguments, $routeMatcherResults, $expected)
    {
        foreach ($testArguments as $testArgumentsItem) {
            $this->provider->addRule(new RequestConstraint(), $testArgumentsItem);
        }

        $this->routeMatcher->method('matches')
            ->willReturnOnConsecutiveCalls(...$routeMatcherResults);

        $testBag = $this->provider->getTests('index', $this->route);

        $this->assertEquals($expected, $testBag);
    }

    public function getTestsDataProvider()
    {
        return [
            [
                [new TestArguments(['ROLE_ADMIN']), new TestArguments(['ROLE_USER'])],
                [false, true],
                new TestBag([new TestArguments(['ROLE_USER'])]),
            ],
            [
                [new TestArguments(['ROLE_ADMIN']), new TestArguments(['ROLE_USER'])],
                [new RequestConstraint('/admin'), true],
                new TestBagMap([
                    [new TestBag([new TestArguments(['ROLE_ADMIN'])]), new RequestConstraint('/admin')],
                    [new TestBag([new TestArguments(['ROLE_USER'])]), null],
                ]),
            ]
        ];
    }

    public function testLogRuntimeMatching()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->provider->setLogger($logger);

        $logger->expects($this->once())
            ->method('warning')
            ->with('Route "index" (path "/") requires runtime matching to access_control rule(s) #0, #1 (zero-based), this would reduce performance.');

        $this->provider->addRule(new RequestConstraint(), new TestArguments(['ROLE_ADMIN']));
        $this->provider->addRule(new RequestConstraint(), new TestArguments(['ROLE_USER']));

        $requestConstraintForMap = new RequestConstraint();

        $this->routeMatcher->method('matches')
            ->willReturnOnConsecutiveCalls($requestConstraintForMap, true);

        $this->provider->getTests('index', $this->route);
    }

    public function testGetTestsWithoutMatches()
    {
        $testBag = $this->provider->getTests('index', $this->route);

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
        $rule = $this->createRuleArray(['allow_if' => 'request.isSecure']);

        $names = ExpressionVoter::getVariableNames();

        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $this->expressionLanguage->expects($this->once())
            ->method('parse')
            ->with($rule['allow_if'], $names)
            ->willReturnCallback(function($expressionString) {
                return new Expression($expressionString);
            });

        $expectedConstraint = new RequestConstraint($rule['path'], $rule['host'], $rule['methods'], $rule['ips']);
        $expectedTestArguments = new TestArguments(array_merge($rule['roles'], [new Expression('request.isSecure')]));

        $this->provider->importRules([$rule]);

        $expectedRules = [[$expectedConstraint, $expectedTestArguments]];
        $this->assertAttributeEquals($expectedRules, 'rules', $this->provider);
    }

    public function testImportRulesWithInvalidExpressionException()
    {
        $rule = $this->createRuleArray(['allow_if' => 'request.isSecure']);

        $this->expressionLanguage->method('parse')
            ->willThrowException(new SyntaxError('syntax'));

        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot parse expression "request.isSecure" with following variables: "token", "user", "object", "subject", "roles", "trust_resolver", "request".');

        $this->provider->importRules([$rule]);
    }

    public function testImportRulesWithExpressionWithoutExpressionLanguage()
    {
        $rule = $this->createRuleArray(['allow_if' => 'request.isSecure']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot create expression because ExpressionLanguage is not provided.');

        $this->provider->importRules([$rule]);
    }

    /**
     * @dataProvider argumentsEqualityDataProvider
     */
    public function testArgumentsEquality($ruleOne, $ruleTwo, $expected)
    {
        $ruleOne = $this->createRuleArray($ruleOne);
        $ruleTwo = $this->createRuleArray($ruleTwo);

        $this->provider->setExpressionLanguage($this->expressionLanguage);

        $this->expressionLanguage->method('parse')
            ->willReturnCallback(function($expressionString) {
                return new Expression($expressionString);
            });

        $this->provider->importRules([$ruleOne, $ruleTwo]);

        $this->routeMatcher->method('matches')
            ->willReturnOnConsecutiveCalls(true, false, true);

        $testBag = $this->provider->getTests('index', $this->route);
        $testArgumentsOne = iterator_to_array($testBag)[0];

        $testBag = $this->provider->getTests('index', $this->route);
        $testArgumentsTwo = iterator_to_array($testBag)[0];

        if ($expected) {
            $this->assertSame($testArgumentsOne, $testArgumentsTwo);
        } else {
            $this->assertNotSame($testArgumentsOne, $testArgumentsTwo);
        }
    }

    public function argumentsEqualityDataProvider()
    {
        return [
            [
                ['roles' => ['ROLE_ADMIN']],
                ['roles' => ['ROLE_ADMIN']],
                true,
            ],
            [
                ['roles' => ['ROLE_ADMIN', 'ROLE_USER']],
                ['roles' => ['ROLE_USER', 'ROLE_ADMIN']],
                true,
            ],
            [
                ['roles' => ['ROLE_ADMIN', 'ROLE_USER']],
                ['roles' => ['ROLE_USER']],
                false,
            ],
            [
                ['roles' => ['ROLE_ADMIN'], 'allow_if' => 'request.isSecure'],
                ['roles' => ['ROLE_ADMIN']],
                false,
            ],
            [
                ['roles' => ['ROLE_ADMIN'], 'allow_if' => 'request.isSecure'],
                ['roles' => ['ROLE_ADMIN'], 'allow_if' => 'request.isSecure'],
                true,
            ],
            [
                ['roles' => ['ROLE_ADMIN'], 'allow_if' => 'request.isSecure'],
                ['roles' => ['ROLE_ADMIN'], 'allow_if' => 'not request.isSecure'],
                false,
            ],
        ];
    }

    private function createRuleArray(array $values = [])
    {
        $defaults = [
            'path' => null,
            'host' => null,
            'methods' => [],
            'ips' => [],
            'allow_if' => null,
            'roles' => [],
        ];

        return array_merge($defaults, $values);
    }
}
