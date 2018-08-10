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
use Yarhon\RouteGuardBundle\DependencyInjection\Compiler\ContainerClassMapPass;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ClassMap;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ClassMapBuilder;

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

        $this->container->register(ClassMap::class)
            ->setArgument(0, [])
            ->setPublic(true);
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
