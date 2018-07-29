<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\SymfonySecurityBundlePass;
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;
use Yarhon\LinkGuardBundle\Security\Provider\SymfonyAccessControlProvider;
use Yarhon\LinkGuardBundle\DependencyInjection\Container\ForeignExtensionAccessor;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonySecurityBundlePassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var SymfonySecurityBundlePass
     */
    private $pass;

    /**
     * @var ForeignExtensionAccessor;
     */
    private $foreignExtensionAccessor;

    private $securityExtension;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->register(AccessMapBuilder::class);

        $this->foreignExtensionAccessor = $this->createMock(ForeignExtensionAccessor::class);

        $this->pass = new SymfonySecurityBundlePass($this->foreignExtensionAccessor);

        $this->securityExtension = $this->createMock('Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension');

        $this->securityExtension->method('getAlias')
            ->willReturn('security');

        $this->container->register(SymfonyAccessControlProvider::class);
    }

    public function testWithoutSecurityBundle()
    {
        $this->pass->process($this->container);

        $methodCalls = $this->container->getDefinition(AccessMapBuilder::class)->getMethodCalls();
        $this->assertCount(0, $methodCalls);

        $this->assertFalse($this->container->hasDefinition(SymfonyAccessControlProvider::class));
    }

    public function testWithSecurityBundleNoAccessControl()
    {
        $this->foreignExtensionAccessor->method('getProcessedConfig')
            ->willReturn([]);

        $this->container->registerExtension($this->securityExtension);

        $this->pass->process($this->container);

        $methodCalls = $this->container->getDefinition(AccessMapBuilder::class)->getMethodCalls();
        $this->assertCount(0, $methodCalls);

        $this->assertFalse($this->container->hasDefinition(SymfonyAccessControlProvider::class));
    }

    public function testWithAccessControl()
    {
        $rules = [
            ['path' => '/path1'],
        ];

        $this->foreignExtensionAccessor->method('getProcessedConfig')
            ->willReturn(['access_control' => $rules]);

        $this->container->registerExtension($this->securityExtension);

        $this->pass->process($this->container);

        $methodCalls = $this->container->getDefinition(AccessMapBuilder::class)->getMethodCalls();
        $this->assertCount(1, $methodCalls);
        list($name, $arguments) = $methodCalls[0];
        $this->assertEquals('addAuthorizationProvider', $name);
        $this->assertInstanceOf(Reference::class, $arguments[0]);
        $this->assertEquals(SymfonyAccessControlProvider::class, (string) $arguments[0]);
    }

    public function testAddRule()
    {
        $rules = [
            ['path' => '/path1'],
            ['path' => '/path2'],
        ];

        $this->foreignExtensionAccessor->method('getProcessedConfig')
            ->willReturn(['access_control' => $rules]);

        $this->container->registerExtension($this->securityExtension);

        $this->pass->process($this->container);

        $methodCalls = $this->container->getDefinition(SymfonyAccessControlProvider::class)->getMethodCalls();
        $this->assertCount(2, $methodCalls);

        list($name, $arguments) = $methodCalls[0];
        $this->assertEquals('addRule', $name);
        $this->assertEquals($rules[0], $arguments[0]);

        list($name, $arguments) = $methodCalls[1];
        $this->assertEquals('addRule', $name);
        $this->assertEquals($rules[1], $arguments[0]);
    }
}
