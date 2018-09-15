<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Controller\ArgumentResolver;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\SessionValueResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentResolverContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SessionValueResolverTest extends TestCase
{
    private $request;

    private $context;

    private $argument;

    public function setUp()
    {
        $this->request = $this->createMock(Request::class);

        $this->context = $this->createMock(ArgumentResolverContext::class);

        $this->context->method('getRequest')
            ->willReturn($this->request);

        $this->argument = $this->createMock(ArgumentMetadata::class);
    }

    public function testSupportsNoSession()
    {
        $resolver = new SessionValueResolver();

        $this->request->method('hasSession')
            ->willReturn(false);

        $this->assertFalse($resolver->supports($this->context, $this->argument));
    }

    public function testSupportsUnsuitableArgumentType()
    {
        $resolver = new SessionValueResolver();

        $this->request->method('hasSession')
            ->willReturn(true);

        $this->argument->method('getType')
            ->willReturn('int');

        $this->assertFalse($resolver->supports($this->context, $this->argument));
    }

    public function testSupportsWithSession()
    {
        $resolver = new SessionValueResolver();

        $session = $this->createMock(SessionInterface::class);

        $this->request->method('hasSession')
            ->willReturn(true);

        $this->request->method('getSession')
            ->willReturn($session);

        $this->argument->method('getType')
            ->willReturn(SessionInterface::class);

        $this->assertTrue($resolver->supports($this->context, $this->argument));
    }

    public function testSupportsWithSessionChild()
    {
        $resolver = new SessionValueResolver();

        $session = $this->createMock(SessionChild::class);

        $this->request->method('hasSession')
            ->willReturn(true);

        $this->request->method('getSession')
            ->willReturn($session);

        $this->argument->method('getType')
            ->willReturn(SessionChild::class);

        $this->assertTrue($resolver->supports($this->context, $this->argument));
    }

    public function testSupportsWithSessionTypeMismatch()
    {
        $resolver = new SessionValueResolver();

        $session = $this->createMock(SessionInterface::class);

        $this->request->method('hasSession')
            ->willReturn(true);

        $this->request->method('getSession')
            ->willReturn($session);

        $this->argument->method('getType')
            ->willReturn(SessionChild::class);

        $this->assertFalse($resolver->supports($this->context, $this->argument));
    }

    public function testResolve()
    {
        $resolver = new SessionValueResolver();

        $session = $this->createMock(SessionInterface::class);

        $this->request->method('getSession')
            ->willReturn($session);

        $this->assertSame($session, $resolver->resolve($this->context, $this->argument));
    }
}

class SessionChild extends Session
{
}
