<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\AuthorizationChecker\AuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\Security\TestLoaderInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Security\RouteAuthorizationChecker;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteAuthorizationCheckerTest extends TestCase
{
    private $testLoader;

    private $authorizationChecker;

    private $routeAuthorizationChecker;

    public function setUp()
    {
        $this->testLoader = $this->createMock(TestLoaderInterface::class);

        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->routeAuthorizationChecker = new RouteAuthorizationChecker($this->testLoader, $this->authorizationChecker);
    }

    /**
     * @dataProvider isGrantedDataProvider
     */
    public function testIsGranted($tests, $authorizationCheckerResults, $expected)
    {
        $this->testLoader->method('load')
            ->willReturn($tests);

        $this->authorizationChecker->method('isGranted')
            ->willReturnOnConsecutiveCalls(...$authorizationCheckerResults);

        $routeContext = new RouteContext('index');

        $this->assertEquals($expected, $this->routeAuthorizationChecker->isGranted($routeContext));
    }

    public function isGrantedDataProvider()
    {
        return [
            [
                [$this->createMock(TestInterface::class), $this->createMock(TestInterface::class)],
                [true, true],
                true,
            ],
            [
                [$this->createMock(TestInterface::class), $this->createMock(TestInterface::class)],
                [false, true],
                false,
            ],
            [
                [$this->createMock(TestInterface::class), $this->createMock(TestInterface::class)],
                [true, false],
                false,
            ],
        ];
    }
}
