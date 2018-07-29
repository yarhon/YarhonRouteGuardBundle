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
use Yarhon\LinkGuardBundle\DependencyInjection\Container\ClassMapBuilder;

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

        $classMapBuilder = $this->createMock(ClassMapBuilder::class);
        $classMapBuilder->method('build')
            ->willReturn(['test_service' => 'test_class']);

        $this->pass = new ContainerClassMapPass($classMapBuilder);

        $definition = new Definition(ClassMap::class, [[]]);
        $definition->setPublic(true);
        $this->container->setDefinition(ClassMap::class, $definition);
    }

    public function testProcess()
    {
        $this->container->compile();
        $this->pass->process($this->container);

        $definition = $this->container->getDefinition(ClassMap::class);
        $arguments = $definition->getArguments();

        $this->assertCount(1, $arguments);

        $expected = [
            'test_service' => 'test_class',
        ];

        $this->assertArraySubset($expected, $arguments[0]);
    }
}
