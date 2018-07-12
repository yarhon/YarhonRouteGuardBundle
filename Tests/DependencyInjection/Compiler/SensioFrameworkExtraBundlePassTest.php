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
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\SensioFrameworkExtraBundlePass;
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;
use Yarhon\LinkGuardBundle\Security\Provider\SensioSecurityProvider;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioFrameworkExtraBundlePassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $builder;

    /**
     * @var SensioFrameworkExtraBundlePass
     */
    private $pass;

    public function setUp()
    {
        $this->builder = new ContainerBuilder();
        $this->builder->register(AccessMapBuilder::class);
        $this->pass = new SensioFrameworkExtraBundlePass();
    }

    public function testProcessWithoutExtraBundle()
    {
        $this->pass->process($this->builder);

        $methodCalls = $this->builder->getDefinition(AccessMapBuilder::class)->getMethodCalls();
        $this->assertCount(0, $methodCalls);

        $this->assertEquals(false, $this->builder->hasDefinition(SensioSecurityProvider::class));
    }

    public function testProcessWithExtraBundle()
    {
        $this->builder->register('sensio_framework_extra.controller.listener');

        $this->pass->process($this->builder);

        $methodCalls = $this->builder->getDefinition(AccessMapBuilder::class)->getMethodCalls();
        $this->assertCount(1, $methodCalls);
        list($name, $arguments) = $methodCalls[0];
        $this->assertEquals('addProvider', $name);
        $this->assertInstanceOf(Reference::class, $arguments[0]);
        $this->assertEquals(SensioSecurityProvider::class, (string) $arguments[0]);
    }
}
