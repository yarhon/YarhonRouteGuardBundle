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
use Yarhon\LinkGuardBundle\DependencyInjection\Configurator\AccessMapBuilderConfigurator;
use Yarhon\LinkGuardBundle\DependencyInjection\Configurator\UrlGeneratorConfigurator;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouterPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

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
        $this->container = new ContainerBuilder();
        $this->pass = new RouterPass();
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testProcessWithoutParameter()
    {
        $this->pass->process($this->container);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function testProcessWithoutRouter()
    {
        $this->container->setParameter($this->parameterName, 'router.default');

        $this->pass->process($this->container);
    }

    public function testProcessAccessMapConfigurator()
    {
        $this->loadBasicConfiguration();

        $this->pass->process($this->container);

        $definition = $this->container->getDefinition(AccessMapBuilderConfigurator::class);
        $arguments = $definition->getArguments();

        $this->assertCount(1, $arguments);
        $this->assertInstanceOf(Reference::class, $arguments[0]);
        $this->assertEquals('router.default', (string) $arguments[0]);
    }

    public function testProcessUrlGeneratorConfigurator()
    {
        $this->markTestIncomplete('Watch UrlGeneratorConfigurator changes.');

        $this->loadBasicConfiguration();

        $this->pass->process($this->container);

        $definition = $this->container->getDefinition('router.default');
    }

    private function loadBasicConfiguration()
    {
        $this->container->setParameter($this->parameterName, 'router.default');
        $this->container->register('router.default');
        $this->container->register(AccessMapBuilderConfigurator::class)->setArgument(0, null);
    }
}
