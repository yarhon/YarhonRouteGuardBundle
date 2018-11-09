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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as BaseAuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Security\Test\SymfonySecurityTest;
use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;
use Yarhon\RouteGuardBundle\Security\AuthorizationChecker\SymfonySecurityAuthorizationChecker;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonySecurityAuthorizationCheckerTest extends TestCase
{
    private $baseAuthorizationChecker;

    private $testResolver;

    private $checker;

    public function setUp()
    {
        $this->baseAuthorizationChecker = $this->createMock(BaseAuthorizationCheckerInterface::class);
        $this->testResolver = $this->createMock(TestResolverInterface::class);
        $this->checker = new SymfonySecurityAuthorizationChecker($this->baseAuthorizationChecker, $this->testResolver);
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports($test, $expected)
    {
        $this->assertEquals($expected, $this->checker->supports($test));
    }

    public function supportsDataProvider()
    {
        return [
            [$this->createMock(TestInterface::class), false],
            [$this->getMockForAbstractClass(SymfonySecurityTest::class, [['ROLE_USER']]), true]
        ];
    }

    /**
     * @dataProvider isGrantedDataProvider
     */
    public function testIsGranted($baseAuthorizationCheckerResult, $expected)
    {
        $test = $this->getMockForAbstractClass(SymfonySecurityTest::class, [['ROLE_USER']]);
        $routeContext = new RouteContext('index');

        $this->testResolver->method('resolve')
            ->with($test, $routeContext)
            ->willReturn(['a', 'b']);

        $this->baseAuthorizationChecker->method('isGranted')
            ->with('a', 'b')
            ->willReturn($baseAuthorizationCheckerResult);

        $this->assertEquals($expected, $this->checker->isGranted($test, $routeContext));
    }

    public function isGrantedDataProvider()
    {
        return [
            [false, false],
            [true, true],
        ];
    }
}
