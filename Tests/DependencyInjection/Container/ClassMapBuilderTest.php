<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\DependencyInjection\Container;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Alias;
use Yarhon\LinkGuardBundle\DependencyInjection\Container\ClassMapBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ClassMapBuilderTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
    }

    public function testBuild()
    {
        $definition1 = new Definition('test_class1', [[]]);
        $definition1->setPublic(true);
        $this->container->setDefinition('test1', $definition1);

        $definition2 = new Definition('test_class2', [[]]);
        $definition2->setPublic(true);
        $this->container->setDefinition('test2', $definition2);

        $alias1 = new Alias('test1');
        $alias1->setPublic(true);
        $this->container->setAlias('test1_alias', $alias1);

        $this->container->compile();

        $builder = new ClassMapBuilder();
        $map = $builder->build($this->container);

        $this->assertInternalType('array', $map);

        $expected = [
            'test1' => 'test_class1',
            'test1_alias' => 'test_class1',
            'test2' => 'test_class2',
        ];

        $this->assertArraySubset($expected, $map);
    }
}
