<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\AuthorizationChecker;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Security\Test\SymfonySecurityTest;
use Yarhon\RouteGuardBundle\Security\AuthorizationChecker\AuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\Security\AuthorizationChecker\DelegatingAuthorizationChecker;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class DelegatingAuthorizationCheckerTest extends TestCase
{
    public function testSupports()
    {
        $delegatingChecker = new DelegatingAuthorizationChecker();

        $test = $this->createMock(TestInterface::class);

        $this->assertTrue($delegatingChecker->supports($test));
    }

    public function testIsGranted()
    {
        $checkers = [
            $this->createMock(AuthorizationCheckerInterface::class),
            $this->createMock(AuthorizationCheckerInterface::class),
        ];

        $checkers[0]->method('supports')
            ->willReturn(false);

        $checkers[1]->method('supports')
            ->willReturn(true);

        $delegatingChecker = new DelegatingAuthorizationChecker($checkers);

        $test = new SymfonySecurityTest(['ROLE_USER']);
        $routeContext = new RouteContext('index');

        $checkers[1]->expects($this->once())
            ->method('isGranted')
            ->with($test, $routeContext)
            ->willReturn(true);

        $this->assertTrue($delegatingChecker->isGranted($test, $routeContext));
    }

    public function testIsGrantedException()
    {
        $checkers = [
            $this->createMock(AuthorizationCheckerInterface::class),
        ];

        $checkers[0]->method('supports')
            ->willReturn(false);

        $delegatingChecker = new DelegatingAuthorizationChecker($checkers);

        $test = new SymfonySecurityTest(['ROLE_USER']);
        $routeContext = new RouteContext('index');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('No authorization checker exists for test instance of "%s".', SymfonySecurityTest::class));

        $delegatingChecker->isGranted($test, $routeContext);
    }
}
