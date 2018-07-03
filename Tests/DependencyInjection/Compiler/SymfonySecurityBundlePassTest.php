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
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\SymfonySecurityBundlePass;
use Yarhon\LinkGuardBundle\Security\AccessMap;
use Yarhon\LinkGuardBundle\Security\Provider\SymfonyAccessControlProvider;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonySecurityBundlePassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $builder;

    /**
     * @var SymfonySecurityBundlePass
     */
    private $pass;

    private $securityExtension;

    public function setUp()
    {
        $this->builder = new ContainerBuilder();
        $this->builder->register(AccessMap::class);
        $this->pass = new SymfonySecurityBundlePass();

        $this->securityExtension = $this->createMock('Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension');

        $this->securityExtension->method('getAlias')
            ->willReturn('security');

        $this->builder->register(SymfonyAccessControlProvider::class);
    }

    public function testProcessWithoutSecurityBundle()
    {
        $this->pass->process($this->builder);

        $methodCalls = $this->builder->getDefinition(AccessMap::class)->getMethodCalls();
        $this->assertCount(0, $methodCalls);

        $this->assertEquals(false, $this->builder->hasDefinition(SymfonyAccessControlProvider::class));
    }

    public function testProcessWithSecurityBundle()
    {
        $this->builder->registerExtension($this->securityExtension);

        $this->pass->process($this->builder);

        $methodCalls = $this->builder->getDefinition(AccessMap::class)->getMethodCalls();
        $this->assertCount(1, $methodCalls);
        list($name, $arguments) = $methodCalls[0];
        $this->assertEquals('addProvider', $name);
        $this->assertEquals(SymfonyAccessControlProvider::class, (string) $arguments[0]);
    }

    public function testAddRules()
    {
        $configs = [
            [],
            [
                'access_control' => [
                    ['path' => '1'],
                ],
            ],
            [
                'access_control' => [
                    ['path' => '2'],
                ],
            ],
        ];

        $this->builder->registerExtension($this->securityExtension);

        foreach ($configs as $config) {
            $this->builder->loadFromExtension('security', $config);
        }

        $this->pass->process($this->builder);

        $methodCalls = $this->builder->getDefinition(SymfonyAccessControlProvider::class)->getMethodCalls();
        $this->assertCount(2, $methodCalls);

        list($name, $arguments) = $methodCalls[0];
        $this->assertEquals('addRule', $name);
        $this->assertEquals(['path' => '1'], $arguments[0]);

        list($name, $arguments) = $methodCalls[1];
        $this->assertEquals('addRule', $name);
        $this->assertEquals(['path' => '2'], $arguments[0]);
    }
}
