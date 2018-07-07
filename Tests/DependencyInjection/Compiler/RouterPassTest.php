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
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\RouterPass;
use Yarhon\LinkGuardBundle\DependencyInjection\Configurator\AccessMapConfigurator;
use Yarhon\LinkGuardBundle\DependencyInjection\Configurator\UrlGeneratorConfigurator;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouterPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $builder;

    /**
     * @var RouterPass
     */
    private $pass;

    /**
     * @var string
     */
    private $parameterName = 'link_guard.router_service_id';

    public function setUp()
    {
        $this->builder = new ContainerBuilder();
        $this->pass = new RouterPass();
    }

    public function testProcessWithoutParameter()
    {
        $this->expectException(ParameterNotFoundException::class);

        $this->pass->process($this->builder);
    }

    public function testProcessWithoutRouter()
    {
        $this->builder->setParameter($this->parameterName, 'router.default');

        $this->expectException(ServiceNotFoundException::class);

        $this->pass->process($this->builder);
    }

    public function testProcessAccessMapConfigurator()
    {
        $this->loadBasicConfiguration();

        $this->pass->process($this->builder);

        $definition = $this->builder->getDefinition(AccessMapConfigurator::class);
        $arguments = $definition->getArguments();
        $this->assertCount(1, $arguments);
        $this->assertInstanceOf(Reference::class, $arguments[0]);
        $this->assertEquals('router.default', (string) $arguments[0]);
    }

    public function testProcessUrlGeneratorConfigurator()
    {
        $this->markTestIncomplete('Watch UrlGeneratorConfigurator changes.');

        $this->loadBasicConfiguration();

        $this->pass->process($this->builder);

        $definition = $this->builder->getDefinition('router.default');
    }

    private function loadBasicConfiguration()
    {
        $this->builder->setParameter($this->parameterName, 'router.default');
        $this->builder->register('router.default');
        $this->builder->register(AccessMapConfigurator::class)->setArgument(0, null);
    }
}
