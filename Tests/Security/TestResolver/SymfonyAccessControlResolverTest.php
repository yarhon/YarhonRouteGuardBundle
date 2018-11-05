<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\TestResolver;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Yarhon\RouteGuardBundle\Security\Test\IsGrantedTest;
use Yarhon\RouteGuardBundle\Routing\RouteContext;
use Yarhon\RouteGuardBundle\Security\TestProvider\SymfonyAccessControlProvider;
use Yarhon\RouteGuardBundle\Security\TestResolver\SymfonyAccessControlResolver;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlResolverTest extends TestCase
{
    private $request;

    private $resolver;

    public function setUp()
    {
        $requestStack = $this->createMock(RequestStack::class);
        $this->request = $this->createMock(Request::class);

        $requestStack->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->resolver = new SymfonyAccessControlResolver($requestStack);
    }

    public function testSupports()
    {
        $test = new IsGrantedTest(['ROLE_USER']);
        $test->setProviderClass(SymfonyAccessControlProvider::class);

        $this->assertTrue($this->resolver->supports($test));
    }

    public function testResolve()
    {
        $test = new IsGrantedTest(['ROLE_USER']);

        $routeContext = new RouteContext('index');

        $resolved = $this->resolver->resolve($test, $routeContext);

        $this->assertSame([['ROLE_USER'], $this->request], $resolved);
    }
}
