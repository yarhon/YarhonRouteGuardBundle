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
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\UrlGeneratorPass;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\UrlGeneratorConfigurator;


/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class UrlGeneratorPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $builder;

    /**
     * @var UrlGeneratorPass
     */
    private $pass;

    public function setUp()
    {
        $this->builder = new ContainerBuilder();
        $this->pass = new UrlGeneratorPass();
    }

    public function testProcessWithoutRouter()
    {
        $this->pass->process($this->builder);

        $this->assertEquals(false, $this->builder->hasDefinition(UrlGeneratorConfigurator::class));
    }

    public function testProcessWithRouter()
    {
        $this->builder->register('router.default');

        $this->pass->process($this->builder);

        $configurator = $this->builder->getDefinition('router.default')->getConfigurator();

        $this->assertEquals(UrlGeneratorConfigurator::class, (string) $configurator[0]);
        $this->assertEquals('configure', $configurator[1]);
    }
}
