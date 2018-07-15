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
use Symfony\Component\DependencyInjection\Definition;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\ContainerClassMapPass;
use Yarhon\LinkGuardBundle\DependencyInjection\Container\ClassMap;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ContainerClassMapPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var ContainerClassMapPass
     */
    private $pass;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->pass = new ContainerClassMapPass();

        $definition = new Definition(ClassMap::class, [[]]);
        $definition->setPublic(true);
        $this->container->setDefinition(ClassMap::class, $definition);
    }

    public function testProcess()
    {
        $definition1 = new Definition('test_class1', [[]]);
        $definition1->setPublic(true);
        $this->container->setDefinition('test1', $definition1);

        $this->container->compile();
        $this->pass->process($this->container);

        $definition = $this->container->getDefinition(ClassMap::class);
        $arguments = $definition->getArguments();

        $this->assertCount(1, $arguments);

        $expected = [
            'test1' => 'test_class1',
        ];

        $this->assertArraySubset($expected, $arguments[0]);
    }
}
