<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Yarhon\RouteGuardBundle\DependencyInjection\Compiler\SymfonySecurityBundlePass;
use Yarhon\RouteGuardBundle\Security\Provider\SymfonyAccessControlProvider;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ForeignExtensionAccessor;

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

        $this->foreignExtensionAccessor = $this->createMock(ForeignExtensionAccessor::class);

        $this->pass = new SymfonySecurityBundlePass($this->foreignExtensionAccessor);

        $this->securityExtension = $this->createMock(Extension::class);

        $this->securityExtension->method('getAlias')
            ->willReturn('security');

        $this->container->register(SymfonyAccessControlProvider::class);
    }

    public function testWithoutSecurityBundle()
    {
        $this->pass->process($this->container);

        $this->assertFalse($this->container->hasDefinition(SymfonyAccessControlProvider::class));
    }

    public function testWithSecurityBundleNoAccessControl()
    {
        $this->foreignExtensionAccessor->method('getProcessedConfig')
            ->willReturn([]);

        $this->container->registerExtension($this->securityExtension);

        $this->pass->process($this->container);

        $this->assertFalse($this->container->hasDefinition(SymfonyAccessControlProvider::class));
    }

    public function testWithSecurityBundleEmptyAccessControl()
    {
        $this->foreignExtensionAccessor->method('getProcessedConfig')
            ->willReturn(['access_control' => []]);

        $this->container->registerExtension($this->securityExtension);

        $this->pass->process($this->container);

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

        $this->assertTrue($this->container->hasDefinition(SymfonyAccessControlProvider::class));
    }

    public function testImportRules()
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
        $this->assertCount(1, $methodCalls);

        list($name, $arguments) = $methodCalls[0];
        $this->assertEquals('importRules', $name);
        $this->assertEquals($rules, $arguments[0]);
    }
}
