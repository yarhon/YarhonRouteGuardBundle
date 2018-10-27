<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Authorization;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Security\Authorization\SensioSecurityExpressionVoter;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityExpressionVoterTest extends TestCase
{
    private $expressionLanguage;

    private $trustResolver;

    private $authChecker;

    private $roleHierarchy;

    private $voter;

    public function setUp()
    {
        $this->expressionLanguage = $this->createMock(ExpressionLanguage::class);
        $this->trustResolver = $this->createMock(AuthenticationTrustResolverInterface::class);
        $this->authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->roleHierarchy = $this->createMock(RoleHierarchyInterface::class);

        $this->voter = new SensioSecurityExpressionVoter($this->expressionLanguage, $this->trustResolver, $this->authChecker);
    }

    public function testVoteNotSupportedAttribute()
    {
        $token = $this->createMock(TokenInterface::class);

        $result = $this->voter->vote($token, null, ['test']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testVote()
    {
        $token = $this->createMock(TokenInterface::class);

        $token->method('getRoles')
            ->willReturn([]);

        $expressionDecorator = $this->createMock(ExpressionDecorator::class);

        $expressionDecorator->method('getVariables')
            ->willReturn(['token' => 'asd', 'custom' => 'test']);

        $expressionDecorator->method('getExpression')
            ->willReturn('expression');

        $expectedVariables = [
            'token' => $token,
            'custom' => 'test',
            'user' => null,
            'object' => null,
            'subject' => null,
            'roles' => [],
            'trust_resolver' => $this->trustResolver,
            'auth_checker' => $this->authChecker,
        ];

        $this->expressionLanguage->expects($this->once())
            ->method('evaluate')
            ->with('expression', $expectedVariables)
            ->willReturn(true);

        $result = $this->voter->vote($token, null, [$expressionDecorator]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testGetVariableNames()
    {
        $expected = [
            'token',
            'user',
            'object',
            'subject',
            'roles',
            'trust_resolver',
            'auth_checker',
            'request',
        ];

        $this->assertSame($expected, SensioSecurityExpressionVoter::getVariableNames());
    }
}
